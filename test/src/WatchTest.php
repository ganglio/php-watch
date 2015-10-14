<?php

namespace WatchTests;

use ganglio\Watch\Watch;

class WatchTest extends \PHPUnit_Framework_TestCase
{
    private $watcher = null;

    protected function setUp()
    {
        $this->watcher = new Watch("./test/fixtures", true);
    }

    protected function tearDown()
    {
        $this->watcher = null;
    }

    public function testGetSetPath()
    {
        $this->watcher->setPath("./test/fixtures/subfolder");
        $this->assertEquals(
            "./test/fixtures/subfolder",
            $this->watcher->getPath()
        );
    }

    public function testGetSetRecursive()
    {
        $this->watcher->setRecursive(false);
        $this->assertFalse(
            $this->watcher->getRecursive()
        );
    }

    public function testNumberOfWatchedObjectsRecursive()
    {
        $this->watcher->setPath("./test/fixtures");
        $this->watcher->setRecursive(true);
        $this->assertEquals(
            9,
            $this->watcher->getNumberOfWatchedObjects()
        );
    }

    public function testNumberOfWatchedObjectsNonRecursive()
    {
        $this->watcher->setPath("./test/fixtures");
        $this->watcher->setRecursive(false);
        $this->assertEquals(
            5,
            $this->watcher->getNumberOfWatchedObjects()
        );
    }

    public function testOnIllegalArgumentExceptionCallback()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_NOT_CLOSURE);
        $this->watcher->on("create", 33);
    }

    public function testOnIllegalArgumentExceptionEvent()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_UNIDENTIFIED_EVENT_NAME);
        $this->watcher->on("destroy", function ($a) {
            return 33;
        });
    }

    public function testOnIllegalArgumentExceptionCallbackParams()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_CALLBACK_FEW_PARAMETERS);
        $this->watcher->on("delete", function () {
            return 33;
        });
    }

    public function testOn()
    {
        $cid = $this->watcher->on("create", function ($a) {
            echo "create callback";
        });

        $this->assertContains(
            $cid,
            $this->watcher->getCallbacks()
        );
    }

    public function testUnbind()
    {
        $cid = $this->watcher->on("create", function ($a) {
            echo "test";
        });

        $this->watcher->unbind($cid);

        $this->assertNotContains(
            $cid,
            $this->watcher->getCallbacks()
        );
    }
}
