<?php

namespace Movim\i18n;

class Locale
{
    private static $_instance;
    public $translations;
    public $language;
    public $hash = [];

    private $iniCache = CACHE_PATH.'locales.ini.cache';

    private function __construct()
    {
        if (file_exists($this->iniCache)) {
            include $this->iniCache;
            $this->hash = $hashes;
        } else {
            $this->compileIni();
            $this->compilePos();
        }
    }

    public function compileIni()
    {
        $this->hash = [];
        $this->loadIni(
            LOCALES_PATH . 'locales.ini',
            true,
            INI_SCANNER_RAW
        );

        $dir = scandir(WIDGETS_PATH);
        foreach ($dir as $widget) {
            $path = WIDGETS_PATH . $widget . '/locales.ini';
            if (file_exists($path)) {
                $this->loadIni($path);
            }
        }

        $locales = fopen($this->iniCache, "w") or die("Unable to open file!");
        fwrite($locales, '<?php' . PHP_EOL . '$hashes = '.var_export($this->hash,true) . ';' . PHP_EOL . '?>');
        fclose($locales);
    }

    public function compilePos()
    {
        // Clear
        foreach (
            glob(
                CACHE_PATH.
                '*.po.cache',
                GLOB_NOSORT
            ) as $cacheFile) {
            @unlink($cacheFile);
        }

        // Cache
        foreach (array_keys($this->getList()) as $language) {
            $this->load($language);

            $locales = fopen(CACHE_PATH.$language.'.po.cache', "w") or die("Unable to open file!");
            fwrite($locales, '<?php' . PHP_EOL . '$translations = '.var_export($this->translations, true) . ';' . PHP_EOL . '?>');
            fclose($locales);
        }
    }

    /**
     * @desc Load a locales ini file and merge it with hash attribute
     * @param $file The path of the fie
     */
    private function loadIni(string $file)
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
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @desc Return an array containing all the presents languages in i18n
     */

    public function getList()
    {
        require_once('languages.php');

        $langList = getLangList();
        $dir = scandir(LOCALES_PATH);
        $po = [];
        foreach ($dir as $files) {
            $explode = explode('.', $files);
            if (end($explode) == 'po'
            && array_key_exists($explode[0], $langList)) {
                $po[$explode[0]] = $langList[$explode[0]];
            }
        }

        $po['en'] = 'English';

        return $po;
    }

    /**
     * @desc Translate a key
     * @param $key The key to translate
     * @param $args Arguments to pass to sprintf
     */
    public function translate(string $key, $args = false): string
    {
        if (empty($key)) {
            return $key;
        }

        $arr = explode('.', $key);

        if (is_array($this->hash)
        && array_key_exists($arr[0], $this->hash)
        && array_key_exists($arr[1], $this->hash[$arr[0]])) {
            $skey = $this->hash[$arr[0]][$arr[1]];

            if ($this->language == 'en') {
                if (is_string($skey)) {
                    $string = $skey;
                } else {
                    $string = $skey[0];
                }
            } elseif (is_array($this->translations)
            && array_key_exists($skey, $this->translations)
            && isset($this->translations[$skey])) {
                $string = $this->translations[$skey];
            } else {
                /*if ($this->language != 'en') {
                    \Utils::info('Locale: Translation not found in ['.$this->language.'] for "'.$key.'" : "'.$skey.'"');
                }*/
                if (is_string($skey)) {
                    $string = $skey;
                } else {
                    \Utils::info('Locale: Double definition for "'.$key.'" got '.serialize($skey));
                    $string = $skey[0];
                }
            }

            if ($args != false) {
                array_unshift($args, $string);
                $string = call_user_func_array("sprintf", $args);
            }

            return $string;
        } else {
            \Utils::info('Locale: Translation key "'.$key.'" not found');
            return $arr[1];
        }
    }

    /**
     * @desc Auto-detects the language from the user browser
     */
    public function detect($accepted = false)
    {
        $langs = [];

        $languages = ($accepted != false) ? $accepted : $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $languages,
            $lang_parse
        );

        if (count($lang_parse[1])) {
            $langs = array_combine($lang_parse[1], $lang_parse[4]);

            foreach ($langs as $lang => $val) {
                if ($val === '') {
                    $langs[$lang] = 1;
                }
            }
            arsort($langs, SORT_NUMERIC);
        }

        foreach ($langs as $key => $value) {
            if (file_exists(LOCALES_PATH . $key . '.po')) {
                $this->language = $key;
                break;
            }

            $exploded = explode('-', $key);
            $key = reset($exploded);

            if (file_exists(LOCALES_PATH . $key . '.po')) {
                $this->language = $key;
                break;
            }

            $this->language = 'en';
        }

        return $this->language;
    }

    /**
     * @desc Load a specific language
     * @param $language The language key to load
     */
    public function load(string $language)
    {
        $this->language = $language;
        $this->loadPo();
    }

    /**
     * @desc Parses a .po file based on the current language
     */
    public function loadPo()
    {
        // Load from the cache
        $cacheFile = CACHE_PATH . $this->language . '.po.cache';
        if (file_exists($cacheFile) && is_readable($cacheFile)) {
            include $cacheFile;
            $this->translations = $translations;
            return;
        }

        $pofile = LOCALES_PATH . $this->language . '.po';
        if (!file_exists($pofile) || !is_readable($pofile)) {
            return false;
        }

        // Parsing the file.
        $handle = fopen($pofile, 'r');

        $this->translations = [];

        $msgid = "";
        $msgstr = "";

        $last_token = "";

        while ($line = fgets($handle)) {
            if ($line[0] == "#"
            || trim(rtrim($line)) == ""
            || preg_match('#^msgctxt#', $line)) {
                continue;
            }

            if (preg_match('#^msgid#', $line)) {
                if ($last_token == "msgstr") {
                    $this->translations[$msgid] = $msgstr;
                }
                $last_token = "msgid";
                $msgid = $this->getQuotedString($line);
            } elseif (preg_match('#^msgstr#', $line)) {
                $last_token = "msgstr";
                $msgstr = $this->getQuotedString($line);
            } else {
                $last_token .= $this->getQuotedString($line);
            }
        }
        if ($last_token == "msgstr") {
            $this->translations[$msgid] = $msgstr;
        }

        fclose($handle);
    }

    private function getQuotedString(string $string)
    {
        $matches = [];
        preg_match('#"(.+)"#', $string, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }
    }
}
