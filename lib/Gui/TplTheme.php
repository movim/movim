<?php

/**
 * @file TplTheme.php
 * This file is part of MOVIM.
 *
 * @brief This objects abstracts a Movim theme and its configuration.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date  1 April 2011
 *
 * Copyright (C) 2011 MOVIM Project.
 *
 * See included COPYING file for licensing details.
 */

class TplTheme
{
    private $regions;
    private $name;
    private $desc;
    private $author;
    private $license;

    private $css;
    private $scripts;

    private $path;

    /**
     * Class constructor.
     * @param theme_name is the theme's name
     */
    public function __construct($theme_name)
    {
        $this->load($theme_name);
    }

    /**
     * Loads up the theme's configuration.
     * @param name is the theme's name.
     */
    private function load($name)
    {
        $this->name = $name;
        $this->path = THEMES_PATH . $this->name . '/';

        $conf = array();
        if(file_exists($this->path . 'conf.xml')) {
            GetConf::convertXmlObjToArr(simplexml_load_file($this->path . 'conf.xml'), $conf);
        } else {
            throw new MovimException(t("Couldn't load file %s", $this->path . 'conf.xml'));
        }

        // Loading the stuff in conf.xml
        $this->name = $conf['info']['name'];
        $this->author = $conf['info']['author'];
        $this->license = $conf['info']['license'];
        $this->desc = $conf['info']['desc'];
        $this->regions = $conf['regions'];
    }

    /**
     * Renders page into the theme.
     */
    public function build($page, $title)
    {
    }

	function addCss($file)
	{
		$this->css[] = $this->link_file($file, true);
	}

    /* Accessors */
    public function Name() { return $this->name; }
    public function Author() { return $this->author; }
    public function License() { return $this->license; }
    public function Desc() { return $this->desc; }
    public function Regions() { return $this->regions; }

    public function Css()
    {
        $out = '';
        $widgets = WidgetWrapper::getInstance();
        $csss = array_merge($this->css, $widgets->loadcss()); // Note the 3rd s, there are many.

        foreach($csss as $css_path) {
			$out .= '<link rel="stylesheet" href="'
				. $css_path .
				"\" type=\"text/css\" />\n";
		}

		return $out;
    }

    public function Scripts()
    {
        return $this->scripts;
    }
}

?>
