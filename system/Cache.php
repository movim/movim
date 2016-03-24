<?php
/**
 * A fully-static class that deals with caching.
 */
class Cache
{
    private static $instance;

    public static function create()
    {
        if(!is_object(self::$instance)) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    // Helper function to access cache.
    public static function c()
    {
        $cache = self::create();

        return call_user_func_array(array($cache, 'handle'), func_get_args());
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

        if(func_num_args() == 1) {
            $content = $this->read_cache($key);

            if(isset($content) && $content != "") {
                return $content;
            } else {
                return null;
            }
        }

        if(func_num_args() == 2) {
            return $this->write_cache($key, $arglist[1]);
        }
        else {
            // Cutting a piece of the args.
            $content = array_slice($arglist, 1);
            return $this->write_cache($key, $content);
        }
    }

    /**
     * Serializes data in a proper fashion.
     */
    private function write_cache($key, $object)
    {
        $data = str_replace("'", "\\'", base64_encode(gzcompress(serialize($object))));
        $time = date(DATE_ISO8601, time());

        $cd = new \modl\CacheDAO();
        $c = new \modl\Cache();

        $c->data = $data;
        $c->name = $key;
        $c->timestamp = $time;

        $cd->set($c);
    }

    /**
     * Unserializes data.
     */
    private function read_cache($key)
    {
        $cd = new \modl\CacheDAO();
        $var = $cd->get($key);

        if(isset($var)) {
            return unserialize(gzuncompress(base64_decode(str_replace("\\'", "'", $var->data))));
        } else {
            return false;
        }
    }
}


?>
