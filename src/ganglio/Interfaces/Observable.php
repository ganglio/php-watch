<?php

namespace ganglio\Watch;

use \ganglio\Watch\Observer;

interface Observable
{
    public function attach(Observer $observer);
    public function detach(Observer $observer);
    public function has(Observer $observer);
    public function notify($args = null);
}
