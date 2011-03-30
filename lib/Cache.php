<?php

/**
 * A fully-static class that deals with caching.
 */
class Cache
{
    private static $login;
    private static $ttl; // TODO

    /**
     * Fetches or commits an object to cache with the provided key.
     *
     * Prototype: Cache::handle(string $key, ...)
     *
     * The following fetches an object from cache.
     *   Cache::handle('key')
     *
     * This commits an object to cache.
     *   Cache::handle('key', $object);
     *
     * Several objects can be commited to cache in this manner:
     *   Cache::handle('key', $object1, $object2, $object3);
     * And retrieved as follows:
     *   list($object1, $object2, $object3) = Cache::handle('key');
     */
    public static function handle($key)
    {
        $arglist = func_get_args();
        $key = $arglist[0];

        // Saving the user's login.
        if(self::$login == "") {
            $user = new User();
            self::$login = $user->getLogin();
        }

        if(func_num_args() == 1) {
            $content = self::read_cache($key);

            if(isset($content) && $content != "") {
                return $content;
            } else {
                return false; // FALSE is better for testing.
            }
        }

        if(func_num_args() == 2) {
            return self::write_cache($key, $arglist[1]);
        }
        else if(func_num_args() > 2) {
            // Cutting a piece of the args.
            $content = array_slice($argslist, 1);
            return self::write_cache($key, $content);
        }
    }

    private static function cache_dir($file = "")
    {
        $cache_dir = BASE_PATH."/user/" . self::$login . "/cache";
        if($file != "") {
            return $cache_dir . '/' . $file;
        } else {
            return $cache_dir;
        }
    }

    /**
     * Serializes data in a proper fashion.
     */
    private static function write_cache($key, $object)
    {
        $s_object = base64_encode(serialize($object));
        $md5 = md5($s_object);

        // Let's see if the cache's dir exists.
        if(!is_dir(self::cache_dir())) {
            mkdir(self::cache_dir(), 0755);
        }

        // OK, writing with its md5 buddy.
        if(!file_put_contents(self::cache_dir($key), $s_object)
           || !file_put_contents(self::cache_dir($key.'.md5'), $md5)) {
            throw new MovimException(sprintf(t("Couldn't set cache file %s"), $key));
        }

        return true; // Just in case.
    }

    /**
     * Unserializes data.
     */
    private static function read_cache($key)
    {
        if(!file_exists(self::cache_dir($key)) || !file_exists(self::cache_dir($key.'.md5'))) {
            return false;
        }

        $s_object = file_get_contents(self::cache_dir($key));
        $md5 = file_get_contents(self::cache_dir($key.'.md5'));

        if(md5($s_object) != $md5) {
            // No good. We summarily clean these files.
            @unlink(self::cache_dir($key));
            @unlink(self::cache_dir($key.'.md5'));
            return false;
        }

        // All good now, unserializing and sending through.
        return unserialize(base64_decode($s_object));
    }
}


?>

