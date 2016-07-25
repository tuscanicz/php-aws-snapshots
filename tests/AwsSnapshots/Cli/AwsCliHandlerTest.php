<?php

namespace AwsSnapshots\Cli;

class AwsCliHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSnapshots()
    {
        $awsCliPath = 'aws';
        $awsCliHandler = new AwsCliHandler($awsCliPath);
        $snapshots = $awsCliHandler->getSnapshots();

        $this->assertGreaterThan(0, count($snapshots->Snapshots));
    }
}
