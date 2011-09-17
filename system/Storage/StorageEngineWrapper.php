<?php

/**
 * Wrapper for default storage engine (convenient for configurable mon-db
 * systems).
 */
class StorageEngineWrapper extends StorageEngineBase implements StorageDriver
{
    private $db;
    private static $driver = null;

    function __construct($conn = "")
    {
        if(!self::$driver) {
            throw new StorageException(t("Unknown storage driver."));
        } else {
            $driver = self::$driver;
            $this->db = new $driver();
            if($conn != "") {
                $this->db->init($conn);
            }
        }
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
            self::$driver = $drivername;
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
