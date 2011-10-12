<?php

/**
 * @file TestStorage.php
 * This file is part of Movim.
 *
 * @brief Tests the Storage module.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 27 April 2011
 *
 * Copyright (C)2011 Movim Project.
 *
 * %license%
 */
define('DB_DEBUG', true);
define('DB_LOGFILE', 'queries.log');

class TestSession
{
    private $db_file;
    private $db;

    function __construct()
    {
        $this->db_file = ut_res('session.db');
        define('TEST_DB_DRIVER', 'sqlite');
        define('TEST_DB_CONN', 'sqlite:///'.$this->db_file);

        storage_load_driver(TEST_DB_DRIVER);
        StorageEngineWrapper::setdriver(TEST_DB_DRIVER, TRUE);

        $this->_wipe();
    }

    private function _wipe()
    {
        if(isset($this->db))
            unset($this->db);

        unlink($this->db_file);
        $this->db = new SQLite3($this->db_file);
    }

    function testSession()
    {
        $sess = Session::start('test');
        // Checking the creation of the table.
        $numtables = $this->db->querySingle('SELECT count(name) as count '.
                                            'FROM sqlite_master WHERE type="table" '.
                                            'AND name="SessionVar"');
        ut_equals($numtables, 1);

        // Inserting
        $sess->set('test', 'stuff');
        $record = $this->db->querySingle(
            'SELECT value FROM SessionVar WHERE name="test"');
        ut_equals(unserialize(base64_decode($record)), 'stuff');

        $var = $sess->get('test');
        ut_equals($var, 'stuff');

        $sess->delete_container();
    }
}

?>
