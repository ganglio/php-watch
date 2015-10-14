<?php

namespace ganglio\Watch;

class Callback
{
    /**
     * a qualifier associated with the callable
     * @var string
     */
    public $type;

    /**
     * the actual callable
     * @var callable
     */
    private $callback;

    /**
     * the constructor
     * @param string   $type
     * @param callable $callback
     */
    public function __construct($type, callable $callback)
    {
        $this->type = $type;
        $this->callback = $callback;
    }

    /**
     * __invoke magic method to expose the class as a callable
     * @return [type] [description]
     */
    public function __invoke()
    {
        return call_user_func_array($this->callback, func_get_args());
    }
}
