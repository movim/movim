<?php

/**
 * @file i18n.php
 * This file is part of MOVIM.
 * 
 * @brief A collection of functions to translate strings.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 22 December 2010
 *
 * Copyright (C)2010 MOVIM team.
 * 
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$language = "";
$translations = array();

/**
 * Translates strings into the given langage.
 */
function t($string)
{
	global $language;
	global $translations;

	if(isset($custom_strings[$string])) {
		return $custom_strings[$string];
	}

	return $string;
}

function get_quoted_string($string)
{
	$matches = array();
	preg_match('#"(.+)"#', $string, $matches);

	return $matches[1];
}

/**
 * Parses a .po file.
 */
function load_language($lang)
{
	global $translations;
	global $language;

	if($lang == $language) {
		return true;
	}

	$pofile = BASE_PATH . '/i18n/' . $lang . '.po';

	if(!file_exists($pofile)) {
		return false;
	}

	// Parsing the file.
	$handle = fopen($pofile, 'r');

	$msgid = "";
	$msgstr = "";

	$last_token = "";

	while($line = fgets($handle)) {
		if($line[0] == "#" || trim(rtrim($line)) == "") {
			continue;
		}
		
		if(preg_match('#^msgid#', $line)) {
			if($last_token == "msgstr") {
				$translations[$msgid] = $msgstr;
			}
			$last_token = "msgid";
			$msgid = get_quoted_string($line);
		}
		else if(preg_match('#^msgstr#', $line)) {
			$last_token = "msgstr";
			$msgstr = get_quoted_string($line);
		}
		else {
			$$last_token .= get_quoted_string($line);
		}
	}
	if($last_token == "msgstr") {
		$translations[$msgid] = $msgstr;
	}
	
	fclose($handle);

	return true;
}

?>
