Create automatic AWS EC2 snapshots with limits on the total number of snapshots created and the interval which the snapshots are created.

For example, you could create a snapshot every day and only keep the last 7 for a running week's worth of snapshots. Or create a snapshot once a week and only keep the last 4 so you would have a running month's worth of snapshots.


## Requirements
- [AWS CLI](http://aws.amazon.com/cli/)
- AWS IAM snapshot permissions ([example policy](#example-iam-policy))
- PHP 5.5+
- Access to crontab (or some other job scheduler)

## AWS CLI installation
```
sudo apt-get install python-pip php5-cli
sudo pip install awscli

// set credentials (Access Key ID, Secret Access Key) and region - ie: us-east-1, us-west-1
aws configure
```

## Setup
This assumes you've already installed and configured [AWS CLI](http://aws.amazon.com/cli/) and added the correct [IAM permissions](#example-iam-policy) within your AWS console.

### 1. Create a PHP script to use the library
Use [composer](https://getcomposer.org) to autoload all the necessary classes.

Setup AWS CLI installation directory (using the value from `which aws`) and 
[configure your volumes](#volume-configuration).

Finally wire the services and test run your cli task. You can also change a description prefix that is used by this library to pick snapshots from the server. This is done via the second argument of `Snapshots::run()`
```php
<?php

$awsCliPath = '/usr/local/bin/aws';
$date = date('Y-m-d');
$volumes = [
   new \AwsSnapshots\Options\VolumeIntervalBackupOptions('vol-123af85a', 7, '1 day', 'dev-server-backup'),
   new \AwsSnapshots\Options\VolumeIntervalBackupOptions('vol-321bg96c', 4, '1 week', 'image-server-' . $date),
   new \AwsSnapshots\Options\VolumeBackupOptions('vol-987ab12a', 10, 'cache-backup-backup'),
];

$awsCliHandler = new \AwsSnapshots\Cli\AwsCliHandler($awsCliPath);

$snapshots = new \AwsSnapshots\Snapshots($awsCliHandler);
$snapshots->run($volumes);
```

#### Example wiring in Symfony services.yml:
```yaml
    aws_snapshots.cli.aws_cli_handler:
        class: AwsSnapshots\Cli\AwsCliHandler
        arguments:
            - '%aws.cli_tool.path%'

    aws_snapshots.snapshots:
        class: AwsSnapshots\Snapshots
        arguments:
            - '@aws_snapshots.cli.aws_cli_handler'
```

#### Volume Configuration
There are two types of volume configurations.

Both of them maintain a certain number of snapshots (snapshot count limit) of a volume with specified description.
The description is used by this library to select snapshots.
When you create a manual snapshot of a same volume, it will be ignored.

##### Interval Backups
You can schedule **only one** backup within a specified interval by using:
```
new VolumeIntervalBackupOptions(volumeId, snapshotCountLimit, interval, description)
```
where the arguments are:

 | Name | Type | Description |
 |------|------|-------------|
 | *volume id* | string | AWS EBS volume ID
 | snapshot count limit | integer | total number of snapshots to store for a volume |
 | interval | string | how often to create snapshot (30 minute, 1 day, 7 day, 2 week - full list below)
 | description | string | snapshot description that shows in the Snapshot section within AWS console and is used to filter manual backups |

##### Regular Backups
You can also backup a volume every time you run the PHP task by using:
```
new VolumeBackupOptions(volumeId, snapshotCountLimit, description)
```
where the arguments are:

| Name | Type | Description |
|------|------|-------------|
| *volume id* | string | AWS EBS volume ID
| snapshot count limit | integer | total number of snapshots to store for a volume |
| description | string | snapshot description that shows in the Snapshot section within AWS console and is used to filter manual backups |

#### Interval Values
The interval format is `number type` (e.g. `30 minute`):

- a number is integer
- a type is one of following units:
  - hour
  - day
  - week
  - month
  - year

### 2. Add a cron job
The cron job schedule will depend on your configuration. You should run the cron command in a period that is as long as the smallest interval of your volume backups.

In the [example](#setup) you have to run the CRON task at least once a day. It will create snapshot of:
 - ```dev-server-backup``` every day at *3:00 am*,
 - ```image-server-${date}``` every week on a day you started it for the first time at *3:00 am*
 - ```cache-server``` every time you run the task (if you run it 3&times; a day it will create 3 snapshots)
```bash
# run the cron job every night at 3:00 am
00	03	* * * /usr/bin/php /root/scripts/run-snapshots.php
```

## Example IAM Policy
This is a minimal policy that includes ONLY the permissions needed to work. You could also limit the "Resources" option to restrict it even further.
```
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ec2:CreateSnapshot",
        "ec2:DeleteSnapshot",
        "ec2:DescribeSnapshots"
      ],
      "Resource": [
        "*"
      ]
    }
  ]
}
```

## Questions, issues or suggestions
Please use the [issues section](https://github.com/tuscanicz/php-aws-snapshots/issues) for any questions or issues you have. Also, suggestions, pull request or any help is most welcome!
