<?php

namespace AwsSnapshots\Options;

class VolumeBackupOptions extends VolumeOptions
{
    public function __construct($volumeId, $snapshotCountLimit, $description)
    {
        parent::__construct($volumeId, $snapshotCountLimit, '0 minute', $description);
    }
}
