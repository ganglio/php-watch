<?php

namespace ganglio\Watch;

interface Observer
{
    public function update($args = null);
}
