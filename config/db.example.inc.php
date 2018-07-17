<?php
# This is the database configuration of Movim
# You need to copy an rename this file to 'db.inc.php' and complete the values
# If using SQLite, only 'type' and 'database' are needed
$conf = [
    # The type can be 'pgsql', 'mysql', or 'sqlite'
    'type'        => 'mysql',
    # The database username
    'username'    => 'username',
    # The password
    'password'    => 'password',
    # Where can we find the database ?
    'host'        => 'localhost',
    # The port number, 3306 for MySQL and 5432 for PostgreSQL
    'port'        => 3306,
    # The database name, or for SQLite the absolute file path
    'database'    => 'movim'
];
