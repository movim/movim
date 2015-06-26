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

require_once('languages.php');

$language = "";
$hash = array();
$translationshash = array();

/**
 * Translates strings into the given langage.
 *
 * This has a sprintf() like behaviour so as to ease translation. Use as:
 *   echo t("my %s string of %d chars", "beautiful", 20);
 *
 * Prototype:
 *   t(string $string, ...)
 */
function t($string)
{
    global $language;
    global $translations;

    $lstring = $string;

    if(isset($translations[$string])) {
        $lstring = $translations[$string];
    }

    // For compiled lang files, set en english default if no translation
    if($lstring == '')
        $lstring = $string;

    if(func_num_args() > 1) {
        $args = func_get_args();
        $args[0] = $lstring; // Replacing with the translated string.
        $lstring = call_user_func_array("sprintf", $args);
    }

    return $lstring;
}

function __() {
    $args = func_get_args();
    global $translationshash;

    $arr = explode('.', $args[0]);

    if(is_array($translationshash) && array_key_exists($arr[0], $translationshash)) {
        $args[0] = $translationshash[$arr[0]][$arr[1]];
        return call_user_func_array('t', $args);
    } else {
        return $args[0];
    }
}

function getQuotedString($string)
{
    $matches = array();
    preg_match('#"(.+)"#', $string, $matches);

    if(isset($matches[1]))
        return $matches[1];
}

/**
 * Parses a .po file.
 */
function parseLangFile($pofile)
{
    if(!file_exists($pofile)) {
        return false;
    }

    // Parsing the file.
    $handle = fopen($pofile, 'r');

    $trans_string = array();

    $msgid = "";
    $msgstr = "";

    $last_token = "";

    while($line = fgets($handle)) {
        if($line[0] == "#" || trim(rtrim($line)) == "") {
            continue;
        }

        if(preg_match('#^msgid#', $line)) {
            if($last_token == "msgstr") {
                $trans_string[$msgid] = $msgstr;
            }
            $last_token = "msgid";
            $msgid = getQuotedString($line);
        }
        else if(preg_match('#^msgstr#', $line)) {
            $last_token = "msgstr";
            $msgstr = getQuotedString($line);
        }
        else {
            $last_token .= getQuotedString($line);
        }
    }
    if($last_token == "msgstr") {
        $trans_string[$msgid] = $msgstr;
    }

    fclose($handle);

    return $trans_string;
}

/**
 * Auto-detects and loads the language.
 */
function loadLanguageAuto()
{
    $langs = array();
    $langNotFound = true;

    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

    if (count($lang_parse[1])) {
        $langs = array_combine($lang_parse[1], $lang_parse[4]);

        foreach ($langs as $lang => $val) {
            if ($val === '') $langs[$lang] = 1;
        }

        arsort($langs, SORT_NUMERIC);
    }

    while((list($key, $value) = each($langs)) && $langNotFound == true) {
        $exploded = explode('-', $key);
        $key = reset($exploded);

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if($key == 'en') {
            loadLanguage($config->locale);
            $langNotFound = false;
        } elseif(file_exists(LOCALES_PATH . $key . '.po')) {
            loadLanguage($key);
            $langNotFound = false;
        }
    }
}

/**
 * Loads the given language.
 */
function loadLanguage($lang)
{
    global $translations;
    global $language;
    global $translationshash;

    if($lang == $language) {
        return true;
    }

    // Here we load the compiled language file
    if(file_exists(CACHE_PATH . '/locales/' . $lang . '.php')) {
        // And we set our global $translations
        require_once(CACHE_PATH . '/locales/' . $lang . '.php');
    } else
        $translations = parseLangFile(LOCALES_PATH . $lang . '.po');

    if(file_exists(LOCALES_PATH . 'locales.ini')) {
        $translationshash = parse_ini_file(LOCALES_PATH . 'locales.ini', true, INI_SCANNER_RAW);
    }

    $language = $lang;

    return true;
}

/**
 * Loads a .po file and adds the translations to the existing ones.
 * Conflicting translation strings will be rejected.
 */
function loadExtraLang($directory)
{
    global $translations;
    global $language;

    // Converting to unix path (simpler and portable.)
    $directory = str_replace('\\', '/', $directory);

    if($directory[-1] != '/') {
        $directory .= '/';
    }

    $trans = parseLangFile($directory . $language . '.po');

    if(!$trans) {
        return false;
    }

    // Merging the arrays. The existing translations have priority.
    foreach($trans as $msgid => $msgstr) {
        if(array_key_exists($msgid, $translations)) {
            continue;
        }
        $translations[$msgid] = $msgstr;
    }

    return true;
}

/**
 * Return an array containing all the presents languages in i18n/
 *
 */

function loadLangArray() {
    $lang_list = get_lang_list();
    $dir = scandir(LOCALES_PATH);
    $po = array();
    foreach($dir as $files) {
        $explode = explode('.', $files);
        if(end($explode) == 'po') {
            $po[$explode[0]] = $lang_list[$explode[0]];
        }
    }

    return $po;
}


?>
