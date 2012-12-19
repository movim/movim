<?php

class CacheVar extends DatajarBase
{
    protected $key;
    protected $data;
    protected $checksum;
    protected $timestamp;

    protected function type_init()
    {
        $this->key       = DatajarType::varchar(128);
        $this->data      = DatajarType::text();
        $this->checksum  = DatajarType::varchar(64);
        $this->timestamp = DatajarType::int();
    }
}


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
    }

    function __destruct()
    {
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

    /**
     * Serializes data in a proper fashion.
     */
    private function write_cache($key, $object)
    {
        $cache_key = $this->login.':'.$key;
        $data = str_replace("'", "\\'", base64_encode(gzcompress(serialize($object))));
        $md5 = md5($data);
        $time = time();

        $var = new CacheVar();

        $query = CacheVar::query()->select()
                                   ->where(array(
                                           'key' => $cache_key))
                                   ->limit(0, 1);
        $result = CacheVar::run_query($query);


        if($result) {
            $var = $result[0];
        }

        $var->key = $cache_key;
        $var->data = $data;
        $var->checksum = $md5;
        $var->timestamp = $time;

        $var->run_query($var->query()->save($var));
    }

    /**
     * Unserializes data.
     */
    private function read_cache($key)
    {
        $cache_key = $this->login.':'.$key;

        $var = new CacheVar();
        if($var->load(array('key' => $cache_key))) {
                        return unserialize(gzuncompress(base64_decode(str_replace("\\'", "'", $var->data))));

        } else {
            return false;
        }
    }
}


?>

