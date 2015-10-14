<?php

namespace WatchTests;

use ganglio\Watch\Callback;

class CallbackTest extends \PHPUnit_Framework_TestCase
{
    public function testCallback()
    {
        $ca = new Callback("test", function ($a) {
            return $a;
        });

        $this->assertEquals(
            "test",
            $ca("test")
        );
    }
}