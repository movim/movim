<?php

/**
 * @file TestStorage.php
 * This file is part of PROJECT.
 *
 * @brief Description
 *
 * @author Guillaume Pasquet <gpasquet@lewisday.co.uk>
 *
 * @version 1.0
 * @date 27 April 2011
 *
 * Copyright (C)2011 Lewis Day Transport Plc.
 *
 * All rights reserved.
 */
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


class TestStorage
{
    function __construct()
    {
        Conf::$conf_path = "tests/php/Storage";
    }

    function testCreate()
    {
        $test = new Account();
        $test->create();

        $db = new SQLite3('tests.db');
        $numtables = $db->querySingle(
            'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
        ut_assert($numtables['count'], 1);
    }
}

?>
