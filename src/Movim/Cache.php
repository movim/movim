<?php

namespace Movim;

/**
 * A fully-static class that deals with caching.
 */
class Cache
{
    private static $_instance;

    public static function create()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new Cache;
        }

        return self::$_instance;
    }

    /**
     * Helper function to access cache.
     */
    public static function c()
    {
        $cache = self::create();

        return call_user_func_array([$cache, 'handle'], func_get_args());
    }

    /**
     * Fetches or commits an object to cache with the provided key.
     *
     * Prototype: handle(string $key, ...)
     *
     * The following fetches an object from cache.
     *   handle('key')
     *
     * This commits an object to cache.
     *   handle('key', $object);
     *
     * Several objects can be commited to cache in this manner:
     *   handle('key', $object1, $object2, $object3);
     * And retrieved as follows:
     *   list($object1, $object2, $object3) = handle('key');
     */
    public function handle($key)
    {
        $arglist = func_get_args();
        $key = $arglist[0];

        if (func_num_args() == 1) {
            $content = $this->_readCache($key);

            if (isset($content) && $content != "") {
                return $content;
            } else {
                return null;
            }
        }

        if (func_num_args() == 2) {
            return $this->_writeCache($key, $arglist[1]);
        }
        // Cutting a piece of the args.
        $content = array_slice($arglist, 1);
        return $this->_writeCache($key, $content);
    }

    /**
     * Serializes data in a proper fashion.
     */
    private function _writeCache($key, $object)
    {
        $data = str_replace(
            "'", "\\'",
            base64_encode(gzcompress(serialize($object)))
        );
        $time = date(\Modl\SQL::SQL_DATE);

        $cd = new \Modl\CacheDAO;
        $c = new \Modl\Cache;

        $c->data = $data;
        $c->name = $key;
        $c->timestamp = $time;

        $cd->set($c);
    }

    /**
     * Unserializes data.
     */
    private function _readCache($key)
    {
        $cd = new \Modl\CacheDAO;
        $var = $cd->get($key);

        if (isset($var)) {
            return unserialize(
                gzuncompress(base64_decode(str_replace("\\'", "'", $var->data)))
            );
        }
        return false;
    }
}


?>
