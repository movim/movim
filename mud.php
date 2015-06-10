#!/usr/bin/php
<?php 
define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/bootstrap.php');

$bootstrap = new Bootstrap();
$bootstrap->boot();

$argsize = count($argv);
if($argsize == 1) {
    echo 
colorize("Welcome to Mud - Movim Unified Doer

Here some requests you can do with me :", 'green')."
- ".colorize("getloc", 'yellow')."    grab all the translations in Movim and generate a global locale file
- ".colorize("comploc", 'yellow')."   compile the current locales to a simple PHP array to boost Movim execution
- ".colorize("comptz", 'yellow')."    compile the timezones
- ".colorize("db", 'yellow')."        create/update the database
- ".colorize("config", 'yellow')."    set the configuration of Movim (separated by commas and colons) eg. info:Test,description:Hop
        ";
    
} elseif($argsize == 2) {
    switch ($argv[1]) {
        case 'getloc':
            getloc();
            break;
        /*case 'comploc':
            comploc();
            break;*/
        case 'comptz':
            comptz();
            break;
        case 'config':
            echo colorize("You need to pass an argument", 'red');
            break;
        case 'db':
            $md = Modl\Modl::getInstance();
            $infos = $md->check();  

            if($infos == null) {
                echo colorize("Nothing to do\n", 'green');
            } else {
                echo colorize("The database need to be updated\n", 'green');
                foreach($infos as $i) {
                    echo colorize($i."\n", 'blue');
                }
            }
            break;
    }
} elseif($argsize == 3) {
    switch ($argv[1]) {
        case 'config':
            config($argv[2]);
            break;
        case 'db':
            if($argv[2] == 'set') {
                $md = Modl\Modl::getInstance();
                $md->check(true);  
                echo colorize("Database updated\n", 'green');
            }
            break;
    }
}

function config($values) {
    echo colorize("Movim configuration setter\n", 'green');

    $cd = new \Modl\ConfigDAO();
    $config = $cd->get();

    $values = explode(',', $values);
    foreach($values as $value) {
        $exp = explode(':', $value);
        $key = $exp[0];
        array_shift($exp);
        $value = implode(':', $exp);

        if(property_exists($config, $key)) {
            $old = $config->$key;
            $config->$key = $value;
            
            $cd->set($config);
            echo colorize("The configuration key ", 'yellow').
                colorize($key, 'red').
                colorize(" has been updated from ", 'yellow').
                colorize($old, 'blue').
                colorize(" to ", 'yellow').
                colorize($value, 'blue')."\n"; 
        }
    }
}

function getloc() {
    echo colorize("Locales grabber\n", 'green');
    
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
    echo colorize("Locales compiler\n", 'green');
    
    $folder = CACHE_PATH.'/locales/';
    
    if(!file_exists($folder)) {
        $bool = mkdir($folder);
        if(!$bool) {
            echo colorize("The locales cache folder can't be created", 'red');
            exit;
        }
    } else
        echo colorize("Folder already exist, don't re-create it\n", 'red');
    
    
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
