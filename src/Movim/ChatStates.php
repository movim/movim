<?php

namespace Movim;

use Movim\Widget\Wrapper;
use React\EventLoop\Timer\Timer;

/**
 * This class handle all the incoming chatstates
 * heal them and merge lists for MUC
 */
class ChatStates
{
    protected static $instance; // Singleton to keep state at all time
    private $_composing = [];
    private $_timeout = 30;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function clearState(string $jid, $resource = null)
    {
        global $loop;

        if (array_key_exists($jid, $this->_composing)) {
            if ($resource !== null) {
                \Utils::debug($resource);
                if (array_key_exists($resource, $this->_composing[$jid])
                && $this->_composing[$jid][$resource] instanceof Timer) {
                    \Utils::debug('clear '.$resource);
                    $loop->cancelTimer($this->_composing[$jid][$resource]);
                    unset($this->_composing[$jid][$resource]);

                    if (empty($this->_composing[$jid])) {
                        unset($this->_composing[$jid]);
                    }
                }
            } elseif ($this->_composing[$jid] instanceof Timer) {
                $loop->cancelTimer($this->_composing[$jid]);
                unset($this->_composing[$jid]);
            }
        }
    }

    public function getState(string $jid)
    {
        return [$jid,
            array_key_exists($jid, $this->_composing)
                ? $this->_composing[$jid]
                : null
        ];
    }

    public function composing(string $from, string $to)
    {
        global $loop;

        $explodedFrom = explodeJid($from);
        $jid = $this->resolveJid($explodedFrom['jid'], $to);

        $timer = $loop->addTimer($this->_timeout, function () use ($from, $to) {
            $this->paused($from, $to);
        });

        if (isset($explodedFrom['resource'])) {
            if (!array_key_exists($jid, $this->_composing)) {
                $this->_composing[$jid] = [];
            }

            /*if (array_key_exists($explodedFrom['resource'], $this->_composing[$jid])
            && $this->_composing[$jid][$explodedFrom['resource']] instanceof Timer) {
                $loop->cancelTimer($this->_composing[$jid][$explodedFrom['resource']]);
                unset($this->_composing[$jid][$explodedFrom['resource']]);
            }*/
            $this->clearState($jid, $explodedFrom['resource']);

            $this->_composing[$jid][$explodedFrom['resource']] = $timer;
        } else {
            /*if (array_key_exists($jid, $this->_composing)
            && $this->_composing[$jid] instanceof Timer) {
                $loop->cancelTimer($this->_composing[$jid]);
                unset($this->_composing[$jid]);
            }*/
            $this->clearState($jid);

            $this->_composing[$jid] = $timer;
        }

        $wrapper = Wrapper::getInstance();
        $wrapper->iterate('composing', [$jid, $this->_composing[$jid]]);
    }

    public function paused(string $from, string $to)
    {
        global $loop;

        $explodedFrom = explodeJid($from);
        $jid = $this->resolveJid($explodedFrom['jid'], $to);

        //if (array_key_exists($jid, $this->_composing)) {
            /*if (isset($explodedFrom['resource'])
            && array_key_exists($explodedFrom['resource'], $this->_composing[$jid])) {
                $loop->cancelTimer($this->_composing[$jid][$explodedFrom['resource']]);
                unset($this->_composing[$jid][$explodedFrom['resource']]);

                if (empty($this->_composing[$jid])) {
                    unset($this->_composing[$jid]);
                }
            } elseif($this->_composing[$jid] instanceof Timer) {
                $loop->cancelTimer($this->_composing[$jid]);
                unset($this->_composing[$jid]);
            }*/

            $this->clearState($jid, $explodedFrom['resource']);

            $wrapper = Wrapper::getInstance();
            $wrapper->iterate('paused', [
                $jid,
                array_key_exists($jid, $this->_composing)
                    ? $this->_composing[$jid]
                    : null
            ]);
        //}
    }

    private function resolveJid(string $from, string $to)
    {
        return ($from == \App\User::me()->id) ? $to : $from;
    }
}