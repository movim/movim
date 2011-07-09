<?php

/**
 * Wrapper for default storage engine (convenient for configurable mon-db
 * systems).
 */
class StorageEngineWrapper implements StorageDriver
{
    private $db;
    private static $driver = null;

    function __construct()
    {
        $driver = $this->driver;
        $this->db = new $driver();
        call_user_func_array(array($this->db, 'init'), func_get_args());
    }

    public static function setdriver($name)
    {
        if(self::$driver != null) {
            throw new StorageException("A storage driver is already loaded.");
        } else {
            $drivername = "StorageEngine".ucfirst(strtolower($name));
            // Is it loaded?
            if(!class_exists($drivername)) {
                storage_load($name);
            }
            $this->driver = $drivername;
        }
    }

    /* Alright now the rest of it is only aliases functions that are redirected
       through the loaded driver.

       Note that call_user_func...() will _NOT_ work here. This is because PHP
       doesn't pass arguments to callbacks by reference, so forget it.
     */
    function create(&$object)
    {
        return $this->db->create($object);
    }

    function save(&$object)
    {
        return $this->db->save($object);
    }

    function delete(&$object)
    {
        return $this->db->delete($object);
    }

    function drop(&$object)
    {
        return $this->db->drop(&$object);
    }

    function load(&$object, array $cond)
    {
        return $this->db->load(&$object, $cond);
    }

    function select($objecttype, array $cond)
    {
        return $this->db->select($objecttype, $cond);
    }
}

?>
