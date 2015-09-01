<?php

class Locale {
    private static $_instance;
    public $translations;
    public $language;
    public $hash = array();

    private function __construct()
    {
        $this->loadIni(
            LOCALES_PATH . 'locales.ini',
            true,
            INI_SCANNER_RAW);

        $dir = scandir(WIDGETS_PATH);
        foreach($dir as $widget) {
            $path = WIDGETS_PATH . $widget . '/locales.ini';
            if(file_exists($path)) {
                $this->loadIni($path);
            }
        }
    }

    /**
     * @desc Load a locales ini file and merge it with hash attribute
     * @param $file The path of the fie
     */
    private function loadIni($file)
    {
        $this->hash = array_merge_recursive(
            $this->hash,
            parse_ini_file(
                $file,
                true,
                INI_SCANNER_RAW
            )
        );
    }

    public static function start()
    {
        if(!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @desc Return an array containing all the presents languages in i18n
     */

    public function getList() {
        require_once('languages.php');

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

    /**
     * @desc Translate a key
     * @param $key The key to translate
     * @param $args Arguments to pass to sprintf
     */
    public function translate($key, $args = false)
    {
        $arr = explode('.', $key);
        if(is_array($this->hash)
        && array_key_exists($arr[0], $this->hash)
        && array_key_exists($arr[1], $this->hash[$arr[0]])) {
            $skey = $this->hash[$arr[0]][$arr[1]];

            if(is_array($this->translations)
            && array_key_exists($skey, $this->translations)
            && isset($this->translations[$skey])) {
                $string = $this->translations[$skey];
            } else {
                if($this->language != 'en') {
                    Utils::log('Locale: Translation not found in ['.$this->language.'] for "'.$key.'" : "'.$skey.'"');
                }
                if(is_string($skey)) {
                    $string = $skey;
                } else {
                    Utils::log('Locale: Double definition for "'.$key.'" got '.serialize($skey));
                    $string = $skey[0];
                }
            }

            if($args != false) {
                array_unshift($args, $string);
                $string = call_user_func_array("sprintf", $args);
            }

            return $string;
        } else {
            Utils::log('Locale: Translation key "'.$key.'" not found');
        }
    }

    /**
     * @desc Auto-detects the language from the user browser
     */
    public function detect()
    {
        $langs = array();

        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            $lang_parse);

        if (count($lang_parse[1])) {
            $langs = array_combine($lang_parse[1], $lang_parse[4]);

            foreach ($langs as $lang => $val) {
                if ($val === '') $langs[$lang] = 1;
            }
            arsort($langs, SORT_NUMERIC);
        }

        while((list($key, $value) = each($langs))) {
            if(file_exists(LOCALES_PATH . $key . '.po')) {
                $this->language = $key;
                return;
            }

            $exploded = explode('-', $key);
            $key = reset($exploded);

            if(file_exists(LOCALES_PATH . $key . '.po')) {
                $this->language = $key;
                return;
            }

            $this->language = 'en';
        }
    }

    /**
     * @desc Load a specific language
     * @param $language The language key to load
     */
    public function load($language)
    {
        $this->language = $language;
        $this->loadPo();
    }

    /**
     * @desc Parses a .po file based on the current language
     */
    public function loadPo()
    {
        $pofile = LOCALES_PATH.$this->language.'.po';
        if(!file_exists($pofile)) {
            return false;
        }

        // Parsing the file.
        $handle = fopen($pofile, 'r');

        $this->translations = array();

        $msgid = "";
        $msgstr = "";

        $last_token = "";

        while($line = fgets($handle)) {
            if($line[0] == "#" || trim(rtrim($line)) == "") {
                continue;
            }

            if(preg_match('#^msgid#', $line)) {
                if($last_token == "msgstr") {
                    $this->translations[$msgid] = $msgstr;
                }
                $last_token = "msgid";
                $msgid = $this->getQuotedString($line);
            }
            else if(preg_match('#^msgstr#', $line)) {
                $last_token = "msgstr";
                $msgstr = $this->getQuotedString($line);
            }
            else {
                $last_token .= $this->getQuotedString($line);
            }
        }
        if($last_token == "msgstr") {
            $this->translations[$msgid] = $msgstr;
        }

        fclose($handle);
    }

    private function getQuotedString($string)
    {
        $matches = array();
        preg_match('#"(.+)"#', $string, $matches);

        if(isset($matches[1]))
            return $matches[1];
    }
}

function __() {
    $args = func_get_args();
    $l = Locale::start();

    $string = array_shift($args);
    return $l->translate($string, $args);
}
