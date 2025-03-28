<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

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
        if ($this->_models->count() > 0) {
            try {
                DB::beginTransaction();

                // We delete all the messages that might already be there
                $table = DB::table('messages');
                $first = $this->_models->first();
                $table = $table->where(function ($query) use ($first) {
                    $query->where('user_id', $first['user_id'])
                          ->where('jidfrom', $first['jidfrom'])
                          ->where('id', $first['id']);
                });

                $this->_models->skip(1)->each(function ($message) use ($table) {
                    $table->orWhere(function ($query) use ($message) {
                        $query->where('user_id', $message['user_id'])
                              ->where('jidfrom', $message['jidfrom'])
                              ->where('id', $message['id']);
                    });
                });
                $table->delete();

                Message::insert($this->_models->toArray());
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }

            $this->_models = collect();
        }

        if ($this->_calls->isNotEmpty()) {
            $this->_calls->each(fn ($call) => $call());
            $this->_calls = collect();
        }
    }

    public function append(Message $message, $call)
    {
        //if (empty($message->mid)) {
            $this->_models[$message->user_id.$message->jidfrom.$message->id] = $message->toRawArray();
            $this->_calls->push($call);
        /*} else {
            $message->save();
            $call();
        }*/
    }
}
