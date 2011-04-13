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
        else {
            // Cutting a piece of the args.
            $content = array_slice($argslist, 1);
            return self::write_cache($key, $content);
        }
    }

    private static function log($text)
    {
        $f = fopen(self::cache_file("queries.log"), "a");
        fwrite($f, time() . ": " . $text . "\n");
        fclose($f);

        return $text;
    }

    private static function cache_file($file = "cache.sqlite")
    {
        return BASE_PATH."/user/" . self::$login . "/" . $file;
    }

    /**
     * Serializes data in a proper fashion.
     */
    private static function write_cache($key, $object)
    {
        $cache_key = self::$login.':'.$key;
        $data = str_replace("'", "\\'", base64_encode(gzcompress(serialize($object))));
        $md5 = md5($data);
        $time = time();

        $db_file = self::cache_file();
        $db = null;

        // Does the database exist?
        if(!file_exists($db_file)) {
            // Creating schema.
            $db = sqlite_open($db_file);

            sqlite_query($db, self::log("CREATE TABLE cache(key VARCHAR(100) UNIQUE, ".
                                        "data TEXT, md5 VARCHAR(32), timestamp TIMESTAMP);"));
        } else {
            $db = sqlite_open($db_file);
        }

        if($db != null) {

            // Does the cache already exist?
            $table = sqlite_array_query($db, self::log("SELECT * FROM cache WHERE key='$cache_key'"));
            if(count($table) > 0) {
                // Need to update.
                sqlite_query($db, self::log("UPDATE cache SET data='$data', md5='$md5', ".
                                            "timestamp='$time' WHERE key='$cache_key'"));
            } else {
                sqlite_query($db, self::log("INSERT INTO cache(key, data, md5, timestamp)".
                                            "VALUES('$cache_key', '$data', '$md5', '$time')"));
            }

            sqlite_close($db);

        } else {
            throw new MovimException(t("Couldn't open cache. Please contact the administrator."));
        }
    }

    /**
     * Unserializes data.
     */
    private static function read_cache($key)
    {
        $cache_key = self::$login.':'.$key;
        $db_file = self::cache_file();

        if(file_exists($db_file)) {
            $db = sqlite_open($db_file);

            // Getting data.
            $table = sqlite_array_query($db, self::log("SELECT * FROM cache WHERE key='$cache_key'"));
            if(count($table) < 1) {
                return false;
            }

            // Checking integrity.
            if(md5($table[0]['data']) == $table[0]['md5']) {
                return unserialize(gzuncompress(base64_decode(str_replace("\\'", "'", $table[0]['data']))));
            } else {
                return false;
            }

        } else {
            return false;
        }
    }
}


?>

