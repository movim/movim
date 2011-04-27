<?php


/**
 * A fully-static class that deals with caching.
 */
class Cache
{
    private static $instance;

    private $db;
    private $log = true;
    private $login;
    private $ttl; // TODO

    // Yes, another singleton...
    private function __construct()
    {
        // Saving the user's login.
        $user = new User();
        $this->login = $user->getLogin();

        $new = false;
        $db_file = $this->cache_file();

        if(!file_exists($db_file)) {
            $new = true;
        }

        try {
            $this->db = new SQLite3($db_file);
        }
        catch(Exception $e) {
            var_dump($this->login);
            echo $e->message;
            exit;
        }

        // Creating schema.
        if($new) {
            $this->query("CREATE TABLE cache(key VARCHAR(100) UNIQUE, ".
                         "data TEXT, md5 VARCHAR(32), timestamp TIMESTAMP);");
        }
    }

    function __destruct()
    {
        $this->db->close();
    }

    private function query($statement, $return = false)
    {
        $this->log($statement);

        if($return) {
            $res = $this->db->query($statement);

            $table = array();
            while($row = $res->fetchArray()) {
                $table[] = $row;
            }
            return $table;
        } else {
            return $this->db->exec($statement);
        }
    }

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
        $cache = Cache::create();

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
                return false; // FALSE is better for testing.
            }
        }

        if(func_num_args() == 2) {
            return $this->write_cache($key, $arglist[1]);
        }
        else {
            // Cutting a piece of the args.
            $content = array_slice($argslist, 1);
            return $this->write_cache($key, $content);
        }
    }

    private function log($text)
    {
        if($this->log) {
            $f = fopen($this->cache_file("queries.log"), "a");
            fwrite($f, time() . ": " . $text . "\n");
            fclose($f);
        }

        return $text;
    }

    private function cache_file($file = "cache.sqlite")
    {
        return BASE_PATH."/user/" . $this->login . "/" . $file;
    }

    /**
     * Serializes data in a proper fashion.
     */
    private function write_cache($key, $object)
    {
        $cache_key = $this->login.':'.$key;
        $data = str_replace("'", "\\'", base64_encode(gzcompress(serialize($object))));
        $md5 = md5($data);
        $time = time();


        if($this->db) {

            // Does the cache already exist?
            $table = $this->query("SELECT count(key) FROM cache WHERE key='$cache_key'", true);
            $this->log(var_export($table, true));
            if(count($table) > 0) {
                // Need to update.
                $this->query("UPDATE cache SET data='$data', md5='$md5', ".
                             "timestamp='$time' WHERE key='$cache_key'");
            } else {
                $this->query("INSERT INTO cache(key, data, md5, timestamp)".
                             "VALUES('$cache_key', '$data', '$md5', '$time')");
            }

        } else {
            throw new MovimException(t("Couldn't open cache. Please contact the administrator."));
        }
    }

    /**
     * Unserializes data.
     */
    private function read_cache($key)
    {
        $cache_key = $this->login.':'.$key;
        $db_file = $this->cache_file();

        if(file_exists($db_file)) {
            // Getting data.
            $table = $this->query("SELECT * FROM cache WHERE key='$cache_key'", true);
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

