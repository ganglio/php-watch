<?php

namespace WatchTests;

use ganglio\Watch\Callback;
use ganglio\Watch\CallbackCollection;

class CallbackCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCallbackCollectionArrayAccessInterface()
    {
        $co = new CallbackCollection();

        $co["test"] = new Callback("test", function () {
            echo "test";
        });

        $this->assertTrue(
            isset($co['test'])
        );

        $this->assertEquals(
            ['test'],
            $co->keys()
        );

        unset($co['test']);

        $this->assertFalse(
            isset($co['test'])
        );

        $co[] = new Callback("test", function () {
            echo "test";
        });

        $this->assertTrue(
            isset($co[0])
        );

        $this->assertEquals(
            "test",
            $co[0]->type
        );
    }

    public function testCallbackCollectionInvokeWithoutType()
    {

        $this->setExpectedException('\InvalidArgumentException', CallbackCollection::ERR_NUM_ARGUMENTS);

        $co = new CallbackCollection();
        $co[] = new Callback("test", function ($a) {
            return $a;
        });

        $co();
    }

    public function testCallbackCollectionInvoke()
    {
        $co = new CallbackCollection();

        $co["cid1"] = new Callback("test", function ($a) {
            return "test1 $a";
        });

        $co["cid2"] = new Callback("test", function ($a) {
            return "test2 $a";
        });

        $out = $co("test",33);

        $this->assertEquals(
            [
                "cid1" => "test1 33",
                "cid2" => "test2 33",
            ],
            $out
        );
    }
}
