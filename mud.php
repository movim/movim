#!/usr/bin/php
<?php 
define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$bootstrap->boot();

$argsize = count($argv);
if($argsize == 1) {
    echo 
"Welcome to Mud - Movim Unified Doer

Here some requests you can do with me :
- getloc    grab all the translations in Movim and generate a global locale file
- comploc   compile the current locales to a simple PHP array to boost Movim execution
        ";
    
} elseif($argsize == 2) {
    switch ($argv[1]) {
        case 'getloc':
            getloc();
            break;
        case 'comploc':
            comploc();
            break;
        case 'comptz':
            comptz();
            break;
    }
}

function getloc() {
    echo "Locales grabber\n";
    
    // We look for all the ini files
    $inifiles = glob(WIDGETS_PATH.'*/*.ini');
    array_push($inifiles, LOCALES_PATH . 'locales.ini');
    
    $locales = CACHE_PATH.'locales.php';
    $pot     = CACHE_PATH.'messages.pot';
    
    // We create the cache file
    $out = "<?php\n";
    
    foreach($inifiles as $ini) {
        $keys = parse_ini_file($ini);
        foreach($keys as $key => $value) {
            $out .= "t(\"$value\");\n";
        }
    }
    
    $fp = fopen($locales, 'w');
    fwrite($fp, $out);
    fclose($fp);
    
    echo "File $locales created\n";
    
    // And we run gettext on it    
    exec("xgettext -e --no-wrap -kt -o $pot -L PHP $locales ");
    
    echo "File $pot created\n";
}

function comploc() {
    echo "Locales compiler\n";
    
    $folder = CACHE_PATH.'/locales/';
    
    if(!file_exists($folder)) {
        $bool = mkdir($folder);
        if(!$bool) {
            echo "The locales cache folder can't be created";
            exit;
        }
    } else
        echo "Folder already exist, don't re-create it\n";
    
    
    $langs = loadLangArray();
    foreach($langs as $key => $value) {
        $langarr = parseLangFile(DOCUMENT_ROOT . '/locales/' . $key . '.po');
        
        $out = '<?php global $translations;
        $translations = array(';
        
        foreach($langarr as $msgid => $msgstr) 
            if($msgid != '')
                $out .= '"'.$msgid.'" => "'. $msgstr . '",'."\n";
            
        $out .= ');';
        
        $fp = fopen($folder.$key.'.php', 'w');
        fwrite($fp, $out);
        fclose($fp);
        
        echo "- $key compiled\n";
    }
}

function comptz() {
    $file = HELPERS_PATH.'TimezoneList.php';
    $tz = generateTimezoneList();

    $out = '<?php global $timezones;
    $timezones = array(';
    
    foreach($tz as $key => $value)
        $out .= '"'.$key.'" => "'. $value . '",'."\n";
        
    $out .= ');';
    
    $fp = fopen($file, 'w');
    fwrite($fp, $out);
    fclose($fp);
    
    echo "- Timezones compiled in $file\n";
}

echo "\n";
