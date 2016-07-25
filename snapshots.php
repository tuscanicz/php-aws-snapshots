<?php

class Snapshots
{
    private $awsCliPath;

    public function __construct($awsCliPath)
    {
        $this->awsCliPath = $awsCliPath;
    }

    public function run(array $volumes)
    {
        foreach ($volumes as $volumeId => $volumeOptions) {
            $options = $this->createOptions($volumeId, $volumeOptions);
            $snapshots = $this->getSnapshots([
                'volume-id' => $volumeId,
                'description' => $options['description']
            ]);
            if ($snapshots) {
                if ($this->shouldCreate($options, $snapshots)) {
                    $this->create($volumeId, $options['description']);
                }
                $this->deleteExtra($options, $volumeId);
            }
        }

        return true;
    }

    /**
     * Check if a snapshot should be created based on the number of snapshots & interval
     *
     * @param array $options
     * @param object $snapshots list of snapshots return from AWS CLI
     * @return boolean
     */
    private function shouldCreate(array $options, $snapshots)
    {
        // create snapshot if none exist and the option is set for 1 or more
        if (count($snapshots->Snapshots) < 1 && $options['snapshots'] > 0) {
            return true;
        }

        $interval = (new \DateTime())->modify('-' . $options['interval']);
        $lastSnapshot = new DateTime(end($snapshots->Snapshots)->StartTime);

        // use same timezones for comparison below
        $interval->setTimezone(new DateTimeZone('EDT'));
        $lastSnapshot->setTimezone(new DateTimeZone('EDT'));

        // check if last snapshot is before the interval time-frame
        if ($lastSnapshot < $interval) {
            return true;
        }

        return false;
    }

    /**
     * Create a new EBS snapshot
     *
     * @param  string $volumeId
     * @param  string $description
     * @return string
     */
    private function create($volumeId, $description)
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
    private function delete($snapshotId)
    {
        $cmd = sprintf($this->awsCliPath . ' ec2 delete-snapshot --snapshot-id %s', escapeshellarg($snapshotId));

        return shell_exec($cmd);
    }

    /**
     * Delete extra snapshots if $snapshot limit is met
     *
     * @param  array $options
     * @param  string $volumeId
     *
     * @return string
     */
    private function deleteExtra(array $options, $volumeId)
    {
        $snapshots = $this->getSnapshots(['volume-id' => $volumeId, 'description' => $options['description']]);
        $snapshotCount = count($snapshots->Snapshots);

        if ($snapshotCount > $options['snapshots']) {
            for ($x = 0; $x < $snapshotCount - $options['snapshots']; ++$x) {
                $this->delete($snapshots->Snapshots[$x]->SnapshotId);
            }
        }
    }

    /**
     * Get list of snapshots based on filters
     *
     * @param  array $filters
     *
     * @return mixed  json object on true
     */
    private function getSnapshots($filters = [])
    {
        $cmd_filters = false;
        foreach ($filters as $name => $value) $cmd_filters .= 'Name=' . escapeshellarg($name) . ',Values=' . escapeshellarg($value) . ' ';

        $cmd = '/usr/local/bin/aws ec2 describe-snapshots ' . ($cmd_filters ? '--filters ' . trim($cmd_filters) : '');
        $response = shell_exec($cmd);

        $snapshots = json_decode($response);
        if (!$snapshots) return false;

        // sort asc by date
        usort($snapshots->Snapshots, function ($a, $b) {
            return strtotime($a->StartTime) - strtotime($b->StartTime);
        });

        return $snapshots;
    }

    /**
     * Sets volume options to current object
     *
     * @param  string $volumeId
     * @param  array $volumeOptions
     * @return array
     */
    private function createOptions($volumeId, array $volumeOptions)
    {
        if (!isset($volumeOptions['snapshots']) || !isset($volumeOptions['interval'])) {
            throw new Exception('Volume ' . $volumeId . ' not ran due to invalid config options');
        }

        return [
            'snapshots' => (int)$volumeOptions['snapshots'],
            'interval' => $volumeOptions['interval'],
            'description' => $volumeOptions['description'],
        ];
    }
}
