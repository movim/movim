<?php

class Session
{
    protected static $instance;
    protected static $sid = null;
    protected $values = array();

    /**
     * Gets a session handle.
     */
    public static function start()
    {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        if(array_key_exists($varname, $this->values)) {
            return unserialize(base64_decode($this->values[$varname]));
        } else {
            return false;
        }
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set($varname, $value)
    {
        $value = base64_encode(serialize($value));
        $this->values[$varname] = $value;

        return $value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        unset($this->values[$varname]);
    }

    /**
     * Deletes all this session container (not the session!)
     */
    public static function dispose()
    {
        if(isset(self::$instance)) {
            self::$instance = null;
            return true;
        } else {
            return false;
        }
    }
}

?>
