<?php

namespace WatchTests;

use ganglio\Watch\Watch;

class WatchTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetPath()
    {
        $watcher = new Watch("./test/fixtures", true);

        $watcher->setPath("./test/fixtures/subfolder");

        $this->assertEquals(
            "./test/fixtures/subfolder",
            $watcher->getPath()
        );
    }

    public function testGetSetRecursive()
    {
        $watcher = new Watch("./test/fixtures", true);

        $watcher->setRecursive(false);

        $this->assertFalse(
            $watcher->getRecursive()
        );
    }

    public function testNumberOfWatchedObjectsRecursive()
    {
        $watcher = new Watch("./test/fixtures", true);

        $watcher->setPath("./test/fixtures");
        $watcher->setRecursive(true);

        $this->assertEquals(
            9,
            $watcher->getNumberOfWatchedObjects()
        );
    }

    public function testNumberOfWatchedObjectsNonRecursive()
    {
        $watcher = new Watch("./test/fixtures", true);

        $watcher->setPath("./test/fixtures");
        $watcher->setRecursive(false);

        $this->assertEquals(
            4,
            $watcher->getNumberOfWatchedObjects()
        );
    }

    public function testOnIllegalArgumentExceptionCallback()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_NOT_CLOSURE);

        $watcher = new Watch("./test/fixtures", true);
        $watcher->on("create", 33);
    }

    public function testOnIllegalArgumentExceptionEvent()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_UNIDENTIFIED_EVENT_NAME);

        $watcher = new Watch("./test/fixtures", true);
        $watcher->on("unknown", function ($a) {
            return 33;
        });
    }

    public function testOnIllegalArgumentExceptionCallbackParams()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_CALLBACK_FEW_PARAMETERS);

        $watcher = new Watch("./test/fixtures", true);
        $watcher->on("delete", function () {
            return 33;
        });
    }

    public function testOn()
    {
        $watcher = new Watch("./test/fixtures", true);

        $cid = $watcher->on("create", function ($a) {
            echo "create callback";
        });

        $this->assertContains(
            $cid,
            $watcher->getCallbacks()
        );
    }

    public function testUnbind()
    {
        $watcher = new Watch("./test/fixtures", true);

        $cid = $watcher->on("create", function ($a) {
            echo "test";
        });

        $watcher->unbind($cid);

        $this->assertNotContains(
            $cid,
            $watcher->getCallbacks()
        );
    }

    public function testOnce()
    {
        $watcher = new Watch("./test/fixtures", true);

        $test_create = null;
        $test_delete = null;
        $test_update = null;

        $watcher->on("create", function ($changed) use (&$test_create) {
            $test_create = $changed;
        });

        $watcher->on("delete", function ($changed) use (&$test_delete) {
            $test_delete = $changed;
        });

        $watcher->on("update", function ($changed) use (&$test_update) {
            $test_update = $changed;
        });

        file_put_contents("./test/fixtures/subfolder/newfile", "test");
        unlink("./test/fixtures/subfolder/subfolder/file7");
        file_put_contents("./test/fixtures/subfolder/file5", "test");

        $watcher->once();

        unlink("./test/fixtures/subfolder/newfile");
        file_put_contents("./test/fixtures/subfolder/subfolder/file7", "");
        file_put_contents("./test/fixtures/subfolder/file5", "");

        $this->assertContains(
            "./test/fixtures/subfolder/newfile",
            $test_create
        );

        $this->assertContains(
            "./test/fixtures/subfolder/file5",
            $test_update
        );

        $this->assertContains(
            "./test/fixtures/subfolder/subfolder/file7",
            $test_delete
        );
    }
}
