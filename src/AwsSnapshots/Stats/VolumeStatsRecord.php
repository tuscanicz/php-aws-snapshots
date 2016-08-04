<?php

namespace AwsSnapshots\Stats;

class VolumeStatsRecord
{
    private $volumeId;
    private $hasNewSnapshots;
    private $deletedSnapshots;

    /**
     * @param string $volumeId
     * @param boolean $hasNewSnapshots
     * @param array $deletedSnapshots
     */
    public function __construct($volumeId, $hasNewSnapshots, $deletedSnapshots)
    {
        $this->volumeId = $volumeId;
        $this->hasNewSnapshots = $hasNewSnapshots;
        $this->deletedSnapshots = $deletedSnapshots;
    }

    public function getVolumeId()
    {
        return $this->volumeId;
    }

    public function isHasNewSnapshots()
    {
        return $this->hasNewSnapshots;
    }

    public function getDeletedSnapshots()
    {
        return $this->deletedSnapshots;
    }
}
