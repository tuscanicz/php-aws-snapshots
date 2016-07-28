<?php

namespace AwsSnapshots\Cli\Filter;

class SnapshotsFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterExact()
    {
        $filter = new SnapshotsFilter('foo', 'bar');

        $this->assertSame('foo', $filter->getName());
        $this->assertSame('bar', $filter->getValue());
        $this->assertSame('bar', $filter->getValueWithAffixes());
    }

    public function testFilterContains()
    {
        $filter = new SnapshotsFilter('foo', 'bar', SnapshotsFilter::MATCH_CONTAINS);

        $this->assertSame('foo', $filter->getName());
        $this->assertSame('bar', $filter->getValue());
        $this->assertSame('*bar*', $filter->getValueWithAffixes());
    }

    public function testFilterBeginsWith()
    {
        $filter = new SnapshotsFilter('foo', 'bar', SnapshotsFilter::MATCH_BEGINS_WITH);

        $this->assertSame('foo', $filter->getName());
        $this->assertSame('bar', $filter->getValue());
        $this->assertSame('bar*', $filter->getValueWithAffixes());
    }

    public function testFilterEndsWith()
    {
        $filter = new SnapshotsFilter('foo', 'bar', SnapshotsFilter::MATCH_ENDS_WITH);

        $this->assertSame('foo', $filter->getName());
        $this->assertSame('bar', $filter->getValue());
        $this->assertSame('*bar', $filter->getValueWithAffixes());
    }
}
