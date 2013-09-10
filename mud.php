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
- comploc   compile the current locales to a simple PHP array to boost Movim execution
        ";
    
} elseif($argsize == 2) {
    switch ($argv[1]) {
        case 'comploc':
            comploc();
            break;
        /*case 1:
            echo "i égal 1";
            break;
        case 2:
            echo "i égal 2";
            break;*/
    }
}

function comploc() {
    echo "Locales compiler\n";
    
    $folder = DOCUMENT_ROOT.'/cache/locales/';
    
    if(!file_exists($folder)) {
        $bool = mkdir($folder);
        if(!$bool) {
            echo "The locales cache folder can't be created";
            exit;
        }
    } else
        echo "Folder already exist, don't re-create it\n";
    
    
    $langs = load_lang_array();
    foreach($langs as $key => $value) {
        $langarr = parse_lang_file(DOCUMENT_ROOT . '/locales/' . $key . '.po');
        
        $out = '<?php global $translations;
        $translations = array(';
        
        foreach($langarr as $msgid => $msgstr) 
            if($msgid != '')
                $out .= '"'.$msgid.'" => "'. $msgstr . '",'."\n";
            
        $out .= ');';
        
        $fp = fopen($folder.$key.'.php', 'w');
        fwrite($fp, $out);
        fclose($fp);
    }
}

echo "\n";
