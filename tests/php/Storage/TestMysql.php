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

class TestMysql
{
    function __construct()
    {
        Conf::$conf_path = "tests/php/Storage";
        storage_load_driver('mysql');
        $this->db_file = ut_res('tests.db');
        $this->_wipe();
    }

    private function _wipe()
    {
        if(!isset($this->db)) {
            $this->db = mysql_connect("localhost", "movim", "movim");
            mysql_select_db("movim", $this->db);
        }

        $result = mysql_query("show tables from movim");
        while($row = mysql_fetch_row($result)) {
            mysql_query("drop table ".$row[0], $this->db);
        }

        if(!isset($this->sdb)) {
            $this->sdb = new StorageEngineMysql("localhost", "3366", "movim", "movim", "movim");
        }
    }

    private function _count($table, $where)
    {
        $query = "SELECT count(*) as count FROM $table WHERE $where";
        $res = mysql_query($query, $this->db);
        $row = mysql_fetch_assoc($res);
        return $row['count'];
    }

    function testCreate()
    {
        $test = new Account();
        $this->sdb->create($test);

        $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
        ut_equals($numtables, 1);
    }

    function testSave()
    {
        $account = new Account();
        $account->balance = 100;
        $account->interest = 0.025;
        $this->sdb->save($account);

        $count = $this->_count('Account', "balance='100'");
        ut_equals($count, 1);
    }

    function testLoad()
    {
        $account = new Account();
        $this->sdb->load($account, array('id' => 1));
        ut_equals($account->balance, 100);
        ut_equals($account->interest, 0.025);
    }

    function testDelete()
    {
        $account = new Account();
        $account->balance = 200;
        $account->interest = 0.020;
        $this->sdb->save($account);

        $count = $this->_count('Account', "balance='200'");
        ut_equals($count, 1);

        $this->sdb->delete($account);

        $count = $this->_count('Account', "balance='200'");
        ut_nassert($account->id);
    }

    function testDrop()
    {
        $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
        ut_equals($numtables, 1);

        $account = new Account();
        $account->balance = 200;
        $account->interest = 0.020;
        $this->sdb->save($account);
        ut_differs($account->id, false);

        $this->sdb->drop($account);

        $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
        ut_equals($numtables, 0);
        ut_nassert($account->id);
    }

    function testSelect()
    {
        // Wiping
        $this->_wipe();

        // Inserting two accounts.
        $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
        $acc2 = new Account(array('balance' => 100, 'interest' => 0.025));

        $this->sdb->create($acc1);
        $this->sdb->save($acc1);
        $this->sdb->save($acc2);

        $objs = $this->sdb->select('Account', array('balance' => 100));

        ut_equals(count($objs), 2);
    }
}

?>
