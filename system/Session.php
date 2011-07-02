<?php

/**
 * @file Session.php
 * This file is part of MOVIM.
 *
 * @brief Class that manages session variables with minimal lock time.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * Yes, this class is again a singleton. But this is justified by the fact that
 * there can only be one active session that is locked down.
 *
 * @version 1.0
 * @date 26 April 2011
 *
 * Copyright (C)2011 MOVIM
 *
 * See COPYING for licensing information.
 */

if(!class_exists('Session')):

class SessionVar extends StorageBase
{
    protected $name;
    protected $value;
    protected $session;

    protected function type_init()
    {
        $this->name    = StorageType::varchar(128);
        $this->value   = StorageType::text();
        $this->session = StorageType::varchar(128);
    }
}
    
class Session
{
    protected $db;
    protected static $instances = array();
    protected static $sid = null;
    protected $container;
    protected $container_id;
    protected $max_age = 3600;

    /**
     * Loads and immediately closes the session variables for the namespace
     * $name.
     */
    protected function __construct($name)
    {
        $db_file = (($_SERVER['DOCUMENT_ROOT'] == "")? dirname(__FILE__) : $_SERVER['DOCUMENT_ROOT']).'/session.db';

        if(defined('SESSION_DB_FILE')) {
            $db_file = SESSION_DB_FILE;
        }

        if(defined('SESSION_MAX_AGE')) {
            $this->max_age = SESSION_MAX_AGE;
        }
        
        // Do we create the schema?
        $create = false;
        if(!file_exists($db_file)) {
            $create = true;
        }

        $this->db = new StorageEngineSqlite($db_file);

        if($create) {
            $var = new SessionVar();
            $this->db->create($var);
        }

        $this->regenerate();
    }

    protected function regenerate()
    {
        // Generating the session cookie's hash.
        $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $hash = "";

        for($i = 0; $i < 64; $i++) {
            $r = mt_rand(0, strlen($hash_chars) - 1);
            $hash.= $hash_chars[$r];
        }

        self::$sid = $hash;
        setcookie('PHPFASTSESSID', self::$sid, time() + $this->max_age);
    }

    /**
     * Gets a session handle.
     */
    public static function start($name)
    {
        if(!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * Commits the session upon destruction.
     */
/*    public function __destruct()
    {
        $this->db->close();
        }*/

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        $data = $this->db->querySingle(
            'SELECT * FROM session_vars WHERE container="'.$this->container_id.'" AND name="'.$this->db->escapeString($varname).'"',
            true);
        if(count($data) > 0) {
            return unserialize(base64_decode($data['value']));
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

        // Does the variable exist?
        $sql = 'SELECT COUNT(name) FROM session_vars '.
            'WHERE container="'.$this->container_id.'" AND name="'.$varname.'"';
        $num_vars = $this->db->querySingle($sql);

        if($num_vars > 0) {
            $sql = 'UPDATE session_vars '.
                'SET value="'.$this->db->escapeString($value).'" '.
                'WHERE container="'.$this->container_id.'" AND name="'.$varname.'"';
        } else {
            $sql = 'INSERT INTO session_vars(container, name, value) '.
                'VALUES("'.$this->container_id.'", "'.$this->db->escapeString($varname).'", "'.
                $this->db->escapeString($value).'")';
        }

        $this->db->exec($sql);

        return $value;
    }

    /**
     * Deletes a variable from the session.
     */
    public function remove($varname)
    {
        return $this->db->exec(
            'DELETE FROM session_vars '.
            'WHERE container="'.$this->container_id.'" '.
            'AND name="'.$this->db->escapeString($varname).'"');
    }

    public function delete_container()
    {
        return $this->db->exec('DELETE FROM session_containers WHERE id="'.$this->container_id.'"');
    }

    /**
     * Deletes all this session container (not the session!)
     */
    public static function dispose($name)
    {
        if(isset(self::$instances[$name])) {
            self::$instances[$name]->delete_container();
            unset(self::$instances[$name]);
            return true;
        } else {
            return false;
        }
    }
}

endif;

?>
