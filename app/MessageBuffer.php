<?php

namespace App;

use App\Message;

class MessageBuffer
{
    protected static $instance;
    private $_models = null;
    private $_calls = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->_models = collect();
        $this->_calls = collect();
    }

    public function save()
    {
        if ($this->_models->isNotEmpty()) {
            try {
                Message::insert($this->_models->toArray());
            } catch (\Exception $e) {
                \Utils::error($e->getMessage());
            }
            $this->_models = collect();
        }

        if ($this->_calls->isNotEmpty()) {
            $this->_calls->each(function ($call) {
                $call();
            });
            $this->_calls = collect();
        }
    }

    public function append(Message $message, $call)
    {
        if ($message->created_at == null && $message->updated_at == null) {
            $this->_models[$message->jidfrom.$message->id] = $message->toRawArray();
            $this->_calls->push($call);
        } else {
            $message->save();
            $call();
        }
    }
}
