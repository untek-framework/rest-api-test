<?php

namespace Untek\Framework\RestApiTest\Asserts;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

class RestApiResponsePathAssert
{

    private Assert $assert;

    public function __construct(private mixed $actual, private RestApiResponseAssert $apiResponseAssert)
    {
        $this->assert = new \Untek\Framework\RestApiTest\Asserts\Assert();
    }

    public function assertEquals($expected): static
    {
        $this->assert->assertEquals($expected, $this->actual);
        return $this;
    }

    public function assertTime(): static
    {
        $this->assertRegexp('^\d{4}-(?:0[1-9]|1[0-2])-(?:[0-2][1-9]|[1-3]0|3[01])T(?:[0-1][0-9]|2[0-3])(?::[0-6]\d)(?::[0-6]\d)?(?:\.\d{3})?(?:[+-][0-2]\d:[0-5]\d|Z)?$');
        return $this;
    }

    public function assertRegexp($exp): static
    {
        if(!preg_match("/$exp/u", $this->actual)) {
            throw new ExpectationFailedException('Not valid value!');
        }
        return $this;
    }

    public function assertNear($expected, $diff = 1): static
    {
        return $this->assertRange($expected - $diff, $expected + $diff);
    }

    public function assertRange($min, $max): static
    {
        $this->assert->assertLessThanOrEqual($max, $this->actual);
        $this->assert->assertGreaterThanOrEqual($min, $this->actual);
        return $this;
    }

    public function getResponseAssert(): RestApiResponseAssert
    {
        return $this->apiResponseAssert;
    }
}
