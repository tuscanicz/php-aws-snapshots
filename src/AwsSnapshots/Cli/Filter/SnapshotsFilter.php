<?php

namespace AwsSnapshots\Cli\Filter;

class SnapshotsFilter
{
    const MATCH_EXACT = 'exact';
    const MATCH_BEGINS_WITH = 'begins';
    const MATCH_ENDS_WITH = 'ends';
    const MATCH_CONTAINS = 'contains';

    private $name;
    private $value;
    private $matchType;

    public function __construct($name, $value, $matchType = self::MATCH_EXACT)
    {
        $this->name = $name;
        $this->value = $value;
        $this->matchType = $matchType;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getMatchType()
    {
        return $this->matchType;
    }

    public function getValueWithAffixes()
    {
        return $this->getFilterPrefix() . $this->getValue() . $this->getFilterSuffix();
    }

    private function getFilterPrefix()
    {
        return $this->getMatchType() === self::MATCH_ENDS_WITH || $this->getMatchType() === self::MATCH_CONTAINS ? '*' : '';
    }

    private function getFilterSuffix()
    {
        return $this->getMatchType() === self::MATCH_BEGINS_WITH || $this->getMatchType() === self::MATCH_CONTAINS ? '*' : '';
    }
}
