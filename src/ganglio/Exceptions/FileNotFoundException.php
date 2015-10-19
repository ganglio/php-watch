<?php

namespace ganglio\Watch\Exceptions;

class FileNotFoundException extends \Exception
{
    private $filename;
    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        parent::__construct("File $filename not found");
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
