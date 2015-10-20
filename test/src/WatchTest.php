<?php

namespace WatchTests;

use ganglio\Watch\Watch;
use ganglio\Watch\FSWatcher;

class WatchTest extends \PHPUnit_Framework_TestCase
{
    public function testOnIllegalArgumentExceptionCallback()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_NOT_CLOSURE);

        $watcher = new Watch(new FSWatcher("./test/fixtures"));
        $watcher->on("create", 33);
    }

    public function testOnIllegalArgumentExceptionEvent()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_UNIDENTIFIED_EVENT_NAME);

        $watcher = new Watch(new FSWatcher("./test/fixtures"));
        $watcher->on("unknown", function ($a) {
            return 33;
        });
    }

    public function testOnIllegalArgumentExceptionCallbackParams()
    {
        $this->setExpectedException('\InvalidArgumentException', Watch::ERR_CALLBACK_FEW_PARAMETERS);

        $watcher = new Watch(new FSWatcher("./test/fixtures"));
        $watcher->on("delete", function () {
            return 33;
        });
    }

    public function testOn()
    {
        $watcher = new Watch(new FSWatcher("./test/fixtures"));

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
        $watcher = new Watch(new FSWatcher("./test/fixtures"));

        $cid = $watcher->on("create", function ($a) {
            echo "test";
        });

        $watcher->unbind($cid);

        $this->assertNotContains(
            $cid,
            $watcher->getCallbacks()
        );
    }
}
