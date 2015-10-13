<?php

namespace ganglio\Watch;

class FSObject
{
    /**
     * The name of the FS object
     * @var string
     */
    public $name = null;

    /**
     * The signature of the FS Object
     * @var string
     */
    public $signature = null;

    public function __construct($name)
    {
        $this->name = $name;

        if (is_dir($name)) {
            $this->signature = md5($name);
        } else if (is_file($name)) {
            $this->signature = md5(file_get_contents($name));
        } else {
            throw new Exceptions\FileNotFoundException($name);
        }
    }
}
