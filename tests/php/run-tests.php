#!/usr/bin/php
<?php

/**
 * @file run-tests.php
 * This file is part of Movicon.
 *
 * @brief This reimplements part of the core into a unit-test
 * framework to test controllers and models.
 *
 * This is meant to be run from the command line.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date  4 March 2011
 *
 * See COPYING for licensing information.
 */

ini_set('error_reporting', E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);

define('TESTSROOT', dirname(__FILE__) . '/');
require('../../loader.php');
require('lib-unit.php');

/*^*********************************
* Testing library.                 *
***********************************/

$failed_tests = array();
$success_tests = array();

/**
 * Returns the full path to the test resources folder.
 */
function ut_res($path)
{
    if(!file_exists(TESTSROOT . 'res')) {
        mkdir(TESTSROOT . 'res');
    }
    return TESTSROOT . 'res/' . $path;
}

/**
 * Saves a test failure details.
 */
function add_failure()
{
  global $failed_tests;
  $trace = debug_backtrace(false);

  for($i = 0; $i < count($trace); $i++) {
    if(!preg_match('/^test/', $trace[$i]['function']))
      continue;

    $failed_tests[] = sprintf('%s::%s Failed %s:%s',
                              $trace[$i]['class'],
                              $trace[$i]['function'],
                              $trace[$i - 1]['file'],
                              $trace[$i - 1]['line']);
  }
}

/**
 * Logs a success.
 */
function add_success()
{
  global $success_tests;

  $trace = debug_backtrace(false);
  foreach($trace as $call) {
    if(!preg_match('/^test/', $call['function']))
      continue;

    $success_tests[] = $call['class'].'::'.$call['function'].' Success';
  }
}

/**
 * Prints out a summary of the tests.
 */
function print_summary()
{
  global $failed_tests;
  global $success_tests;

  $success = count($success_tests);
  $failures = count($failed_tests);
  $total = $success + $failures;

  print "\n";
  print "Test summary:\n";
  printf("  Total tests:\t\t %d\n", $total);
  printf("  Success:\t\t %d\n", $success);
  printf("  Failures:\t\t %d\n", $failures);
  printf("  Success rate:\t\t %d%%\n", ($success / $total) * 100);

  if($failures) {
    // Printing errors details.
    print "\n";
    print "Failures:\n";
    foreach($failed_tests as $test) {
      printf("%s\n", $test);
    }
  }
}

/**
 * Does the job of printing and keeping records about the tests.
 */
function printtest($passed)
{
  if(!$passed) {
    add_failure();
  } else {
    add_success();
  }

  if(!$passed) {
    printf("x");
  } else {
    printf('.');
  }
}

function test_path($path = '')
{
    return './' . $path;
}

/**
 * Loads up an application.
 */
function load_app($app)
{
  global $mvc_app;

  $mvc_app = array(
    'name' => $app,
    'dir' => mvc_get_conf('directories/apps', 'apps').'/'.$app,
    );

  mvc_load_conf($mvc_app['dir'] . '/app.ini');

  $components = mvc_get_conf('app/components');
  if($components != "") {
    mvc_load_components(explode(':', $components));
  }
}

function app_is_loaded($app = false)
{
  global $mvc_app;

  if($app) {
    return (is_array($mvc_app) &&
            isset($mvc_app['name']) &&
            $mvc_app['name'] == $app);
  } else {
    return (is_array($mvc_app) &&
            isset($mvc_app['name']) &&
            isset($mvc_app['dir']));
  }
}

function run_testsuite($suite)
{
  $testdir = opendir(test_path($suite));
  printf("Suite %s\n", $suite);

  while($test = readdir($testdir)) {
    // Is that a PHP file?
    if(!preg_match('#\.php$#i', $test)) {
      continue;
    }

    printf("  File %s\n", $test);
    load_tests(test_path($suite.'/'), $test);
  }
  closedir($testdir);
}

/**
 * Runs a test suite directory.
 */
function run_tests($testname = NULL)
{
  if($testname) {
    run_testsuite($testname);
  } else {
    $testdir = opendir(test_path());
    while($suite = readdir($testdir)) {
      if(!is_dir(test_path($suite))
         || preg_match('#^\.+$#', $suite)
         || $suite == 'res') continue;

      run_testsuite($suite);
    }
  }

  print_summary();

  closedir($testdir);
}

function load_tests($dir, $testname)
{
  require($dir . $testname);
  $classname = ucfirst(substr($testname, 0, strlen($testname) - 4));

  if(!class_exists($classname)) {
      echo "Warning: class $classname doesn't exist.\n";
      return;
  }

  $refl = new ReflectionClass($classname);
  $meths = $refl->getMethods();
  $class = new $classname();

  foreach($meths as $method) {
    $name = $method->getName();
    if(!preg_match('#^test#', $name)) {
      continue;
    }

    printf("    Running %s ", $name);
    $class->$name();

    print "\n";
  }
}

/**
 * Help.
 */
function usage()
{
  printf("Runs unit tests.\n");
  printf("%s [test suite]\n", __FILE__);
}

/**
 * Main function.
 */
function main($argc, $argv)
{
  if($argc < 1 || $argc > 2) {
    return usage();
  }

  if($argc == 2)
    run_tests($argv[1]);
  else
    run_tests();
}

// Starts
main($argc, $argv);

?>