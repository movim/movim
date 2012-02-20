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
if(function_exists("mysql_connect")) {
    define('DB_DEBUG', true);
    define('DB_LOGFILE', 'queries.log');

    if(!class_exists('Account')) {
        class Account extends DatajarBase
        {
            // Storable fields.
            protected $balance;
            protected $interest;
            protected $owners;

            protected function type_init()
            {
                $this->balance = DatajarType::float();
                $this->interest = DatajarType::float();
            }
        }
    }

    if(!class_exists('Owner')) {
        class Owner extends DatajarBase
        {
            protected $name;
            protected $dob;
            protected $account;

            protected function type_init()
            {
                $this->name = DatajarType::varchar(256);
                $this->dob = DatajarType::date();
                $this->foreignkey('account', 'Account');
            }
        }
    }

    class TestMysql
    {
        private $host = "localhost";
        private $port = "3366";
        private $username = "movim";
        private $password = "movim";
        private $schema = "movim";

        function __construct()
        {
            Conf::$conf_path = "tests/php/Datajar";
            datajar_load_driver('mysql');
            $this->_wipe();
        }

        private function _wipe()
        {
            if(!isset($this->db)) {
                $this->db = mysql_connect($this->host.':'.$this->port,
                                          $this->username,
                                          $this->password);
                mysql_select_db($this->schema, $this->db);
            }

            $result = mysql_query("show tables from movim");
            while($row = mysql_fetch_row($result)) {
                mysql_query("drop table ".$row[0], $this->db);
            }

            if(!isset($this->sdb)) {
                $this->sdb = new DatajarEngineMysql("mysql://".$this->username.":".$this->password."@".$this->host.":".$this->port."/".$this->schema);
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

        function testOrder()
        {
            // Wiping
            $this->_wipe();
            
            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));
            $this->sdb->create($acc1);
            $this->sdb->save($acc1);
            $this->sdb->save($acc2);

            $objs = $this->sdb->select("Account", array(), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);
            
            $objs = $this->sdb->select("Account", array(), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $objs = $this->sdb->select("Account", array('interest' => 0.015), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);
            
            $objs = $this->sdb->select("Account", array('interest' => 0.015), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);
        }
    }
}

?>
