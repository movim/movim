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
     * Loads up the theme's files and configuration.
     * @param name is the theme's name.
     */
    private function load($name)
    {
        $this->name = $name;
        $this->path = THEMES_PATH . $this->name . '/';

        if(file_exists($this->path . 'conf.xml')) {
            $this->conf = simplexml_load_file($this->path . 'conf.xml');
        } else {
            throw new MovimException(t("Couldn't load file %s", $this->path . 'conf.xml'));
        }
    }
}

?>
