<?php

namespace AwsSnapshots\Cli;

use AwsSnapshots\Cli\Filter\SnapshotsFilter;

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
     * @param  SnapshotsFilter[] $filters
     * @return mixed json object on true
     */
    public function getSnapshots($filters = [])
    {
        $cmdFilters = '';
        foreach ($filters as $filter) {
            $cmdFilters .= 'Name=' . escapeshellarg($filter->getName()) . ',Values=' . escapeshellarg($filter->getValueWithAffixes()) . ' ';
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
        $cmd = sprintf($this->awsCliPath . ' ec2 create-snapshot --volume-id %s --description %s', escapeshellarg($volumeId), escapeshellarg($description));

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
