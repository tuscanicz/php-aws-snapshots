<?php

namespace Tuscanicz\AwsSnapshots\Shell;

class AwsCliHandler
{
    private $awsCliPath;

    public function __construct($awsCliPath)
    {
        $this->awsCliPath = $awsCliPath;
    }

    /**
     * Get list of snapshots based on filters
     *
     * @param  array $filters
     * @return mixed json object on true
     */
    public function getSnapshots($filters = [])
    {
        $cmdFilters = '';
        foreach ($filters as $name => $value) {
            $cmdFilters .= 'Name=' . $name . ',Values=' . $value . ' ';
        }

        $cmd = $this->awsCliPath . ' ec2 describe-snapshots ' . ($cmdFilters !== '' ? '--filters ' . trim($cmdFilters) : '');
        $response = shell_exec($cmd);

        $snapshots = json_decode($response);
        if (!$snapshots) {
            return false;
        }

        // sort asc by StartTime
        usort($snapshots->Snapshots, function ($a, $b) {
            return strtotime($a->StartTime) - strtotime($b->StartTime);
        });

        return $snapshots;
    }

    /**
     * Create a new EBS snapshot
     *
     * @param  string $volumeId
     * @param  string $description
     * @return string
     */
    public function createSnapshot($volumeId, $description)
    {
        $cmd = sprintf($this->awsCliPath . ' ec2 create-snapshot --volume-id %s --description "' . $description . '"', escapeshellarg($volumeId));

        return shell_exec($cmd);
    }

    /**
     * Delete a snapshot
     *
     * @param  string $snapshotId
     * @return string
     */
    public function deleteSnapshot($snapshotId)
    {
        $cmd = sprintf($this->awsCliPath . ' ec2 delete-snapshot --snapshot-id %s', escapeshellarg($snapshotId));

        return shell_exec($cmd);
    }
}
