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

class Session
{
    protected $db;
    protected static $instances = array();
    protected $sid;
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
        $this->db = new SQLite3($db_file);

        $this->container = $this->db->escapeString($name);

        if($create) { // Creating the session schema.
            $this->db->exec('CREATE TABLE IF NOT EXISTS sessions(hash VARCHAR(64) PRIMARY KEY, timestamp INTEGER)');
            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS session_containers('.
                'id INTEGER PRIMARY KEY AUTOINCREMENT, '.
                'hash VARCHAR(64) REFERENCES sessions(hash) ON DELETE CASCADE '.
                'name VARCHAR(128))'
                );
            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS session_vars('.
                'container INTEGER REFERENCES session_containers(id) ON DELETE CASCADE '.
                'name VARCHAR(128), '.
                'value TEXT'
                );
        }

        if(isset($_COOKIE['PHPFASTSESSID'])) {
            $sessid = $this->db->escapeString($_COOKIE['PHPFASTSESSID']);
            $session = $this->db->querySingle('SELECT * FROM sessions WHERE hash="'.$sessid.'"', true) or die("SQL error");
            if(count($session) == 0) {
                $this->regenerate();
            }
            else if($session['timestamp'] + $this->max_age > time()) {
                $this->db->exec('DELETE FROM sessions WHERE hash="'.$sessid.'"');
                $this->regenerate();
            }
            else {
                $this->sid = $sessid;
            }
        } else {
            $this->regenerate();
        }

        // Does the container exist?
        $num_container = $this->db->querySingle('SELECT id FROM session_containers WHERE hash="'.$this->sid.'" AND name="'.$this->container.'"') or die("SQLite Error.");
        if($num_container == NULL) {
            $this->db->exec('INSERT INTO session_containers(hash, name) VALUES("'.$this->sid.'", "'.$this->container.'")');
            $this->container_id = $this->db->lastInsertRowID();
        } else {
            $this->container_id = $this->db->escapeString($num_container);
        }
    }

    protected function regenerate()
    {
        // Generating the session cookie's hash.
        $hash_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $hash = "";

        $exists = true;
        while($exists = true) {
            for($i = 0; $i < 64; $i++) {
                $r = rand(0, strlen($hash_chars) - 1);
                $hash.= $hash_chars[$r];
            }

            $num = $this->db->querySingle('SELECT count(hash) FROM sessions WHERE hash="'.$this->db->escapeString($hash).'"');
            if($num == 0) {
                $exists = false;
            }
        }

        $this->sid = $this->db->escapeString($hash);
        $this->db->exec('INSERT INTO sessions(hash, timestamp) VALUES("'.$this->sid.'", "'.time().'")') or die("SQLITE error!");
        setcookie('PHPFASTSESSID', $this->sid);
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
    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * Gets a session variable. Returns false if doesn't exist.
     */
    public function get($varname)
    {
        $data = $this->db->querySingle(
            'SELECT * FROM session_vars WHERE container="'.$this->container_id.'"',
            true);
        if(count($data) > 0) {
            return $data['value'];
        } else {
            return false;
        }
    }

    /**
     * Sets a session variable. Returns $value.
     */
    public function set($varname, $value)
    {
        // Does the variable exist?
        $num_vars = $this->db->querySingle(
            'SELECT COUNT(name) FROM session_vars '.
            'WHERE container="'.$this->container_id.'" AND name="'.$varname.'"');
        if($num_vars > 0) {
            $this->db->exec(
                'UPDATE session_vars '.
                'SET value="'.$this->db->escapeString($value).'" '.
                'WHERE container="'.$this->container_id.'" AND name="'.$varname.'"');
        } else {
            $this->db->exec(
                'INSERT INTO session_vars(name, value) '.
                'VALUES("'.$this->db->escapeString($varname).'", "'.
                $this->db->escapeString($value).'")');
        }

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
    public static function destroy($name)
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
