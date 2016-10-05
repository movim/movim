<?php
namespace Movim\Task;

use React\EventLoop\LoopInterface;

class Engine
{
    private static $_instance;
    public $_loop;

    private function __construct(LoopInterface $loop)
    {
        $this->_loop = $loop;
    }

    public static function init(LoopInterface $loop)
    {
        if(!isset(self::$_instance)) {
            self::$_instance = new self($loop);
        } else {
            self::start();
        }
    }

    public static function start()
    {
        return self::$_instance;
    }
}
