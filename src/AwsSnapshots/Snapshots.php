<?php

namespace AwsSnapshots;

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
     */
    public function run(array $volumes)
    {
        foreach ($volumes as $volume) {
            if ($this->shouldCreate($volume)) {
                $this->awsCliHandler->createSnapshot($volume->getVolumeId(), $volume->getDescription());
            }
            $this->deleteExtra($volume);
        }
    }

    /**
     * Check if a snapshot should be created based on the number of snapshots & interval
     *
     * @param VolumeOptions $options
     * @return boolean
     */
    private function shouldCreate(VolumeOptions $options)
    {
        $snapshots = $this->awsCliHandler->getSnapshots([
            'volume-id' => escapeshellarg($options->getVolumeId()),
            'description' => 'scheduled-snapshot-' . escapeshellarg($options->getDescription())
        ]);

        // should create a snapshot if none exist and have to be at least one
        if (count($snapshots->Snapshots) < 1 && $options->getSnapshotCountLimit() > 0) {
            return true;
        }

        $interval = (new \DateTime())->modify('-' . $options->getInterval());
        $lastSnapshot = new \DateTime(end($snapshots->Snapshots)->StartTime);

        // use same timezones for comparison below
        $interval->setTimezone(new \DateTimeZone('EDT'));
        $lastSnapshot->setTimezone(new \DateTimeZone('EDT'));

        // should create a snapshot if last one is before the interval time-frame
        if ($lastSnapshot < $interval) {
            return true;
        }

        return false;
    }

    /**
     * Delete extra snapshots if $snapshot limit is met
     *
     * @param  VolumeOptions $options
     *
     * @return string
     */
    private function deleteExtra(VolumeOptions $options)
    {
        $snapshots = $this->awsCliHandler->getSnapshots([
            'volume-id' => escapeshellarg($options->getVolumeId()),
            'description' => 'scheduled-snapshot-*'
        ]);
        $snapshotCount = count($snapshots->Snapshots);

        if ($snapshotCount > $options->getSnapshotCountLimit()) {
            for ($x = 0; $x < $snapshotCount - $options->getSnapshotCountLimit(); ++$x) {
                $this->awsCliHandler->deleteSnapshot($snapshots->Snapshots[$x]->SnapshotId);
            }
        }
    }
}
