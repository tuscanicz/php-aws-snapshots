<?php

namespace AwsSnapshots\Options;

abstract class VolumeOptions
{
    const VOLUMES_DESCRIPTION_DEFAULT_PREFIX = 'scheduled-snapshot-';
    const VOLUMES_DESCRIPTION_EMPTY_PREFIX = '';

    private $volumeId;
    private $snapshotCountLimit;
    private $interval;
    private $description;

    /**
     * @param string $volumeId
     * @param int $snapshotCountLimit
     * @param string $interval
     * @param string $description
     */
    public function __construct($volumeId, $snapshotCountLimit, $interval, $description)
    {
        $this->volumeId = $volumeId;
        $this->snapshotCountLimit = $snapshotCountLimit;
        $this->interval = $interval;
        $this->description = $description;
    }

    public function getVolumeId()
    {
        return $this->volumeId;
    }

    public function getSnapshotCountLimit()
    {
        return $this->snapshotCountLimit;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
