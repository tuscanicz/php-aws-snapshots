<?php

namespace AwsSnapshots\Stats;

use Exception;

class VolumeStats
{
    private $volumeStats;

    /**
     * @param VolumeStatsRecord[] $volumeStats
     */
    public function __construct(array $volumeStats)
    {
        $this->volumeStats = $volumeStats;
    }

    public function getBackedVolumes()
    {
        $backedVolumes = [];
        foreach ($this->volumeStats as $volumeStats) {
            if ($volumeStats->isHasNewSnapshots()) {
                $backedVolumes[] = $volumeStats->getVolumeId();
            }
        }

        return $backedVolumes;
    }

    public function getSkippedVolumes()
    {
        $skippedVolumes = [];
        foreach ($this->volumeStats as $volumeStats) {
            if ($volumeStats->isHasNewSnapshots() === false) {
                $skippedVolumes[] = $volumeStats->getVolumeId();
            }
        }

        return $skippedVolumes;
    }

    public function getDeletedSnapshots()
    {
        $deletedSnapshots = [];
        foreach ($this->volumeStats as $volumeStats) {
            $deletedSnapshots = array_merge($deletedSnapshots, $volumeStats->getDeletedSnapshots());
        }

        return $deletedSnapshots;
    }

    public function getDeletedSnapshotsByVolumeId($volumeId)
    {
        foreach ($this->volumeStats as $volumeStats) {
            if ($volumeStats->getVolumeId() == $volumeId) {
                return $volumeStats->getDeletedSnapshots();
            }
        }
        throw new Exception('Volume-id not found: ' . $volumeId);
    }

    public function getBackedVolumesCount()
    {
        return count($this->getBackedVolumes());
    }

    public function getSkippedVolumesCount()
    {
        return count($this->getSkippedVolumes());
    }

    public function getDeletedSnapshotsCount()
    {
        return count($this->getDeletedSnapshots());
    }
}
