<?php

namespace App;

use App\Message;

class BundleCapabilityResolver
{
    protected static $instance;

    private $_bundles = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->_bundles = collect();
    }

    public function load()
    {
        $this->_bundles = Bundle::where('user_id', \App\User::me()->id)
                                ->whereNull('node')
                                ->get()
                                ->map(function ($bundle) {
                                    return $bundle->jid.'_'.$bundle->bundleid;
                                });
    }

    public function resolve(Message $message)
    {
        if ($this->_bundles->contains($message->jidfrom.'_'.$message->bundleid)) {
            $presence = Presence::where('jid', $message->jidfrom)
                    ->where('resource', $message->resource)
                    ->with('capability')
                    ->first();

            if ($presence && $presence->capability) {
                Bundle::where('user_id', \App\User::me()->id)
                      ->whereNull('node')
                      ->where('jid', $message->jidfrom)
                      ->where('bundleid', $message->bundleid)
                      ->update(['node' => $presence->node]);

                $this->_bundles = $this->_bundles->reject(function ($value, $key) use ($message) {
                    return $value == $message->jidfrom.'_'.$message->bundleid;
                });
            }
        }
    }
}
