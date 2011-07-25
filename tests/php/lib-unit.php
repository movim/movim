<?php

/**
 * @file lib-unit.php
 * This file is part of Movicon.
 * 
 * @brief Unit-testing library for Movicon.
 *
 * @author Guillaume Pasquet <gpasquet@lewisday.co.uk>
 *
 * @version 1.0
 * @date  7 March 2011
 *
 * Copyright (C)2011 Movicon project.
 * 
 * See COPYING for licensing information.
 */


/**
 * Signals an error if the contained code returns false.
 */
function ut_assert($stuff)
{
  printtest($stuff);
}

function ut_nassert($stuff)
{
  printtest(!$stuff);
}

/**
 * Checks if $stuff is equal to $expectedstuff.
 */
function ut_equals($stuff, $expectedstuff)
{
  if(is_array($stuff) && is_array($expectedstuff)) {
    $stuff = sort($stuff);
    $expectedstuff = sort($expectedstuff);
  }
  printtest($stuff == $expectedstuff);
}

function ut_differs($stuff, $expectedstuff)
{
  if(is_array($stuff) && is_array($expectedstuff)) {
    $stuff = sort($stuff);
    $expectedstuff = sort($expectedstuff);
  }
  printtest($stuff != $expectedstuff);
}

?>
