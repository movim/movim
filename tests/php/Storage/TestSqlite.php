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

if(!class_exists('Account')) {
    class Account extends StorageBase
    {
        // Storable fields.
        protected $balance;
        protected $interest;
        protected $owners;

        protected function type_init()
        {
            $this->balance = StorageType::float();
            $this->interest = StorageType::float();
        }
    }
}

if(!class_exists('Owner')) {
    class Owner extends StorageBase
    {
        protected $name;
        protected $dob;
        protected $account;

        protected function type_init()
        {
            $this->name = StorageType::varchar(256);
            $this->dob = StorageType::date();
            $this->foreignkey('account', 'Account');
        }
    }
}

class TestSqlite
{
    function __construct()
    {
        Conf::$conf_path = "tests/php/Storage";
        storage_load_driver('sqlite');
        $this->db_file = ut_res('tests.db');
        $this->_wipe();
    }

    private function _wipe()
    {
        if(isset($this->sdb))
            unset($this->sdb);
        if(isset($this->db))
            unset($this->db);

        unlink($this->db_file);
        $this->sdb = new StorageEngineSqlite($this->db_file);
        $this->db = new SQLite3($this->db_file);
        StorageBase::bind($this->sdb);
    }

    function testCreate()
    {
        $test = new Account();
        $this->sdb->create($test);

        $numtables = $this->db->querySingle(
            'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
        ut_equals($numtables, 1);

        $this->_wipe();
        $test->create();
        $numtables = $this->db->querySingle(
            'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
        ut_equals($numtables, 1);
    }

    function testPopulate()
    {
        $vals = array('balance' => 50, 'interest' => 0.01);

        $test = new Account();
        $test->populate($vals);
        ut_equals($test->balance, 50);
        ut_equals($test->interest, 0.01);

        $account = new Account($vals);
        ut_equals($account->balance, 50);
        ut_equals($account->interest, 0.01);
    }

    function testSave()
    {
        $account = new Account();
        $account->balance = 100;
        $account->interest = 0.025;
        $this->sdb->save($account);

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="100" AND interest="0.025"');
        ut_equals($count, 1);

        $account->balance = 200;
        $account->interest = 0.015;
        $account->save();

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="200" AND interest="0.015"');
        ut_equals($count, 1);
    }

    function testLoad()
    {
        $account = new Account();
        $this->sdb->load($account, array('id' => 1));
        ut_equals($account->balance, 200);
        ut_equals($account->interest, 0.015);

        $account = null;
        $account = new Account();
        $account->load(array('id' => 1));
        ut_equals($account->balance, 200);
        ut_equals($account->interest, 0.015);
    }

    function testDelete()
    {
        $account = new Account();
        $account->balance = 200;
        $account->interest = 0.020;
        $this->sdb->save($account);

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="200" AND interest="0.020"');
        ut_equals($count, 1);

        $this->sdb->delete($account);

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="200" AND interest="0.020"');
        ut_equals($count, 0);

        ut_nassert($account->id);

        $account = null;
        $account = new Account();
        $account->balance = 200;
        $account->interest = 0.020;
        $account->save();

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="200" AND interest="0.020"');
        ut_equals($count, 1);

        $account->delete();

        $count = $this->db->querySingle(
            'SELECT count(*) as count FROM Account '.
            'WHERE balance="200" AND interest="0.020"');
        ut_equals($count, 0);

        ut_nassert($account->id);
    }

    function testDrop()
    {
        $numtables = $this->db->querySingle(
            'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
        ut_equals($numtables, 1);

        $account = new Account();
        $account->balance = 200;
        $account->interest = 0.020;
        $this->sdb->save($account);
        ut_differs($account->id, false);

        $this->sdb->drop($account);

        $numtables = $this->db->querySingle(
            'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
        ut_equals($numtables, 0);

        ut_nassert($account->id);
    }

    function testSelect()
    {
        // Wiping
        $this->_wipe();

        // Inserting two accounts.
        $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
        $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));

        $acc1->create();
        $acc1->save();
        $acc2->save();

        $objs = Account::select(array('interest' => 0.015));

        ut_equals(count($objs), 2);
    }

    function __destruct()
    {
    }
}

?>
