<?php

namespace AwsSnapshots\Options;

class VolumeIntervalBackupOptions extends VolumeOptions
{
    public function __construct($volumeId, $snapshotCountLimit, $interval, $description)
    {
        parent::__construct($volumeId, $snapshotCountLimit, $interval, $description);
    }
}
