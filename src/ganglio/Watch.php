<?php

namespace ganglio\Watch;

class Watch implements Observer
{
    const ERR_NOT_CLOSURE             = 1;
    const ERR_UNIDENTIFIED_EVENT_NAME = 2;
    const ERR_CALLBACK_FEW_PARAMETERS = 4;

    /**
     * The list of all the registered callbacks
     * @var CallbackCollection
     */
    private $callbacks;

    /**
     * The observable object
     * @var null
     */
    private $observable;

    /**
     * Constructor
     * @param string  $path      The path to watch
     * @param boolean $recursive Recurse subdirectory?
     */
    public function __construct(Observable $observable)
    {
        $this->observable = $observable;
        $this->observable->attach($this);

        $this->callbacks = new CallbackCollection();
    }

    /**
     * returns the list of create callbacks ids
     * @return array<string>
     */
    public function getCallbacks()
    {
        return $this->callbacks->keys();
    }

    /**
     * Binds a callback to a change event
     * @param  string  $event
     * @param  Closure $callback
     * @return string  a unique id for the callback. Can be used to unbind
     */
    public function on($event, $callback)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                "Argument 2 need to be an instance of \Closure",
                self::ERR_NOT_CLOSURE
            );
        }

        $numArgs = (new \ReflectionFunction($callback))->getNumberOfParameters();

        if ($numArgs < 1) {
            throw new \InvalidArgumentException(
                "Callback need at least one parameter",
                self::ERR_CALLBACK_FEW_PARAMETERS
            );
        }

        $callback_id = spl_object_hash($callback);

        if (!in_array($event, ['create', 'delete', 'update'])) {
            throw new \InvalidArgumentException(
                "Argument 2 need to be either 'create', 'delete' or 'update'",
                self::ERR_UNIDENTIFIED_EVENT_NAME
            );
        }

        $this->callbacks[$callback_id] = new Callback($event, $callback);

        return $callback_id;
    }

    /**
     * Unbind callback
     * @param  string $cid
     */
    public function unbind($cid)
    {
        unset($this->callbacks[$cid]);
    }

    /**
     * Implements the update method defined by Observer
     * @param  array<string, array<string>> $args
     * @return void
     */
    public function update($args = null)
    {
        $callbacks = $this->callbacks; // ugly php<5.6 hack

        foreach ($args as $type => $changes) {
            $callbacks($type, $changes);
        }
    }
}
