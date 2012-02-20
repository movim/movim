<?php

/**
 * @file TestDatajar.php
 * This file is part of Movim.
 *
 * @brief Tests the Datajar module.
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
    private $sess;

    function __construct()
    {
        $this->db_file = ut_res('session.db');
        define('TEST_DB_DRIVER', 'sqlite');
        define('TEST_DB_CONN', 'sqlite:///'.$this->db_file);

        datajar_load_driver(TEST_DB_DRIVER);
        DatajarEngineWrapper::setdriver(TEST_DB_DRIVER, TRUE);

        $this->_wipe();
    }

    private function _wipe()
    {
        if(isset($this->db))
            unset($this->db);

        unlink($this->db_file);
        $this->db = new SQLite3($this->db_file);
    }

    function testCreate()
    {
        $this->sess = Session::start('test');
        // Checking the creation of the table.
        $numtables = $this->db->querySingle('SELECT count(name) as count '.
                                            'FROM sqlite_master WHERE type="table" '.
                                            'AND name="SessionVar"');
        ut_equals($numtables, 1);
    }

    function testSet() {
        // Inserting
        $this->sess->set('test', 'stuff');
        $record = $this->db->querySingle(
            'SELECT value FROM SessionVar WHERE name="test"');
        ut_equals(unserialize(base64_decode($record)), 'stuff');

        $var = $this->sess->get('test');
        ut_equals($var, 'stuff');
    }

    function testRemove() {
        $this->sess->remove('test');

        $record = $this->db->querySingle(
            'SELECT COUNT(value) FROM SessionVar WHERE name="test"');

        ut_equals($record, 0);
    }

    function testDispose() {
        $sess2 = Session::start('foobar');
        $sess2->set('foo', 'bar');
        $this->sess->set('foo', 'baz');

        $this->sess->dispose('foobar');

        $record = $this->db->querySingle(
            'SELECT COUNT(value) FROM SessionVar WHERE name="foo" AND container="foobar"');
        ut_equals($record, 0);

        $record = $this->db->querySingle(
            'SELECT COUNT(value) FROM SessionVar WHERE name="foo" AND container="test"');
        ut_differs($record, 0);
    }
}

?>
