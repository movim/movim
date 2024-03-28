<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\i18n;

enum Dir: string
{
    case LTR = 'ltr';
    case RTL = 'rtl';
};

class Locale
{
    private static $instance;
    public const DEFAULT_LANGUAGE = 'en';
    public const DEFAULT_DIRECTION = Dir::LTR;
    public const LOCALE_REGEXP = '(?<language>[a-z]{2,8})(?:[-_](?<script>[A-Za-z][a-z]{3}))?(?:[-_](?<region>[A-Za-z]{2,3}|[0-9]{3}))?';
    public const RTL_LANGUAGES = ['ar', 'he', 'fa', 'ur', 'ps', 'syr', 'dv'];
    public const RTL_SCRIPTS = ['Adlm', 'Arab', 'Aran', 'Armi', 'Avst', 'Cprt', 'Hebr', 'Khar', 'Lydi', 'Mand', 'Mani', 'Mend', 'Narb', 'Nbat', 'Nkoo', 'Orkh', 'Palm', 'Phli', 'Phlp', 'Phnx', 'Prti', 'Samr', 'Sarb', 'Syrc', 'Thaa'];
    public $translations;
    public $language;
    public $hash = [];

    private $iniCache = CACHE_PATH . 'locales.ini.cache';

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
        fwrite($locales, '<?php' . PHP_EOL . '$hashes = ' . var_export($this->hash, true) . ';' . PHP_EOL . '?>');
        fclose($locales);
    }

    public function compilePos()
    {
        // Clear
        foreach (glob(
                CACHE_PATH .
                    '*.po.cache',
                GLOB_NOSORT
            ) as $cacheFile) {
            @unlink($cacheFile);
        }

        // Cache
        foreach (array_keys($this->getList()) as $language) {
            $this->load($language);

            $locales = fopen(CACHE_PATH . $language . '.po.cache', "w") or die("Unable to open file!");
            fwrite($locales, '<?php' . PHP_EOL . '$translations = ' . var_export($this->translations, true) . ';' . PHP_EOL . '?>');
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
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
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
            if (
                end($explode) == 'po'
                && array_key_exists($explode[0], $langList)
            ) {
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

        if (
            is_array($this->hash)
            && array_key_exists($arr[0], $this->hash)
            && array_key_exists($arr[1], $this->hash[$arr[0]])
        ) {
            $skey = $this->hash[$arr[0]][$arr[1]];

            if ($this->language == 'en') {
                if (is_string($skey)) {
                    $string = $skey;
                } else {
                    $string = $skey[0];
                }
            } elseif (
                is_array($this->translations)
                && array_key_exists($skey, $this->translations)
                && isset($this->translations[$skey])
            ) {
                $string = $this->translations[$skey];
            } else {
                /*if ($this->language != 'en') {
                    \Utils::info('Locale: Translation not found in ['.$this->language.'] for "'.$key.'" : "'.$skey.'"');
                }*/
                if (is_string($skey)) {
                    $string = $skey;
                } else {
                    \Utils::info('Locale: Double definition for "' . $key . '" got ' . serialize($skey));
                    $string = $skey[0];
                }
            }

            if ($args != false) {
                array_unshift($args, $string);
                $string = call_user_func_array("sprintf", $args);
            }

            return $string;
        } else {
            \Utils::info('Locale: Translation key "' . $key . '" not found');
            return $arr[1];
        }
    }

    /**
     * @desc Poor man’s locale_parse, but looking to save dependencies & resources
     */
    public static function parseStr(string $str): ?array {
        if (preg_match('/' . self::LOCALE_REGEXP . '/', $str, $loc)) {
            self::reformatLocalePartsToISO639($loc);
            return $loc;
        }

        return null;
    }

    private static function reformatLocalePartsToISO639(array &$locale) {
        foreach ($locale as $key => &$value) {
            if (is_numeric($key) || empty($value)) {
                unset($locale[$key]);
            } else {
                $locale[$key] = match ($key) {
                    'language' => strtolower($value),
                    'script' => ucfirst(strtolower($value)),
                    'region' => strtoupper($value),
                    default => $value,
                };
            }
        };

        if (empty($locale)) {
            $locale = null;
        }
    }

    /**
     * @desc Auto-detects the language from the user browser
     */
    public function detect(?string $languages = null): ?string
    {
        if (!isset($this->language)) {
            $this->language = self::DEFAULT_LANGUAGE;
        }

        $rexp = '/' . self::LOCALE_REGEXP . '\s*(?:;\s*(Q|q)\s*=\s*(?<quality>1|0\.[0-9]+))?/';

        if (preg_match_all($rexp, $languages ?? $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', $locs, PREG_SET_ORDER)) {
            foreach($locs as &$loc) {
                if (isset($loc['quality']) && !empty($loc['quality'])) {
                    $loc['quality'] = floatval($loc['quality']);
                } else {
                    $loc['quality'] = 1.0;
                }
                self::reformatLocalePartsToISO639($loc);
            }

            usort($locs, function($a, $b) {
                return $a['quality'] - $b['quality'];
            });

            $poFileExists = function (array $l): ?array {
                $lang = strtolower(implode('_', array_values($l)));
                return [$lang, file_exists(LOCALES_PATH . $lang . '.po')];
            };

            foreach ($locs as &$loc) {
                // ``quality`` is no longer needed after sorting
                unset($loc['quality']);

                [$lang, $exists] = $poFileExists($loc);
                if ($exists) {
                    $this->language = $lang;
                    break;
                }

                if (isset($loc['script'])) {
                    unset($loc['script']);
                    [$lang, $exists] = $poFileExists($loc);
                    if ($exists) {
                        $this->language = $lang;
                        break;
                    }
                }

                if (isset($loc['region'])) {
                    unset($loc['region']);
                    [$lang, $exists] = $poFileExists($loc);
                    if ($exists) {
                        $this->language = $lang;
                        break;
                    }
                }
            }
        }

        return $this->language;
    }

    /**
     * @desc Load a specific language
     * @param $language The language key to load
     */
    public function load(string $language)
    {
        $this->language = $this->printPo($language);
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
            if (
                $line[0] == "#"
                || trim(rtrim($line)) == ""
                || preg_match('#^msgctxt#', $line)
            ) {
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

    /**
     * @desc Determine the direction of a locale string
     */
    public static function getDirection(string $str): Dir
    {
        $loc = self::parseStr($str);

        if (empty($loc)) {
            return self::DEFAULT_DIRECTION;
        }

        if (isset($loc['script'])) {
            return in_array($loc['script'], self::RTL_SCRIPTS) ? Dir::RTL : Dir::LTR;
        }

        return in_array($loc['language'], self::RTL_LANGUAGES) ? Dir::RTL : Dir::LTR;
    }

    /**
     * @desc Converts a string to Locale, then prints as an ISO-639-compatbile string
     */
    public static function printISO639(string $str): string
    {
        $parsed = self::parseStr($str);
        return is_array($parsed) ? implode('-', array_values($parsed)) : $str;
    }

    /**
     * @desc Converts a string to Locale, then prints as an POSIX-compatbile string
     */
    public static function printPOSIX(string $str): string
    {
        $parsed = self::parseStr($str);
        return is_array($parsed) ? implode('_', array_values($parsed)) : $str;
    }

    /**
     * @desc Converts a string to Locale, then prints as an PO-filename-compatbile string
     */
    public static function printPo(string $str): string
    {
        return strtolower(self::printPOSIX($str));
    }
}
