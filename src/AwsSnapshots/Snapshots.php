<?php

namespace AwsSnapshots;

use AwsSnapshots\Cli\Filter\SnapshotsFilter;
use AwsSnapshots\Options\VolumeIntervalBackupOptions;
use AwsSnapshots\Options\VolumeOptions;
use AwsSnapshots\Cli\AwsCliHandler;

class Snapshots
{
    private $awsCliHandler;

    public function __construct(AwsCliHandler $awsCliHandler)
    {
        $this->awsCliHandler = $awsCliHandler;
    }

    /**
     * @param VolumeOptions[] $volumes
     * @param string $volumesPrefix = Options::VOLUMES_DESCRIPTION_DEFAULT_PREFIX (optional)
     */
    public function run(array $volumes, $volumesPrefix = VolumeOptions::VOLUMES_DESCRIPTION_DEFAULT_PREFIX)
    {
        foreach ($volumes as $volume) {
            if ($this->shouldCreate($volume, $volumesPrefix)) {
                $this->awsCliHandler->createSnapshot($volume->getVolumeId(), $volumesPrefix . $volume->getDescription());
            }
            $this->deleteExtra($volume, $volumesPrefix);
        }
    }

    /**
     * Check if a snapshot should be created based on the number of snapshots & interval
     *
     * @param VolumeOptions $options
     * @param string $volumesPrefix
     * @return boolean
     */
    private function shouldCreate(VolumeOptions $options, $volumesPrefix)
    {
        // Should create a snapshot if the volume is not backed in intervals.
        if (get_class($options) !== VolumeIntervalBackupOptions::class) {
            return true;
        }

        $snapshots = $this->awsCliHandler->getSnapshots([
            new SnapshotsFilter('volume-id', $options->getVolumeId()),
            new SnapshotsFilter('description', $volumesPrefix, SnapshotsFilter::MATCH_BEGINS_WITH)
        ]);
        $snapshotCount = (!$snapshots) ? 0 : count($snapshots->Snapshots);

        // Should create a snapshot if there are none of them created and have to be.
        if ($snapshotCount < 1 && $options->getSnapshotCountLimit() > 0) {
            return true;
        }

        $shouldNotCreateDateTime = (new \DateTime('+ 3 minute'))->modify('-' . $options->getInterval());
        $latestVolumeSnapshotDateTime = new \DateTime(end($snapshots->Snapshots)->StartTime);

        $shouldNotCreateDateTime->setTimezone(new \DateTimeZone('EDT'));
        $latestVolumeSnapshotDateTime->setTimezone(new \DateTimeZone('EDT'));

        // Should create a snapshot if the last one was created before the specified interval.
        if ($latestVolumeSnapshotDateTime < $shouldNotCreateDateTime) {
            return true;
        }

        return false;
    }

    /**
     * Delete extra snapshots if $snapshot limit is met
     *
     * @param  VolumeOptions $options
     * @param  string $volumesPrefix
     */
    private function deleteExtra(VolumeOptions $options, $volumesPrefix)
    {
        $snapshots = $this->awsCliHandler->getSnapshots([
            new SnapshotsFilter('volume-id', $options->getVolumeId()),
            new SnapshotsFilter('description', $volumesPrefix, SnapshotsFilter::MATCH_BEGINS_WITH)
        ]);
        $snapshotCount = (!$snapshots) ? 0 : count($snapshots->Snapshots);

        if ($snapshotCount > $options->getSnapshotCountLimit()) {
            for ($x = 0; $x < $snapshotCount - $options->getSnapshotCountLimit(); ++$x) {
                $this->awsCliHandler->deleteSnapshot($snapshots->Snapshots[$x]->SnapshotId);
            }
        }
    }
}
