<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim\Controller;

use Movim\Template\Builder;
use Movim\Route;

class Base
{
    public $name = 'main';          // The name of the current page
    public $unique = false;         // Only one WS for this page
    protected $session_only = false;// The page is protected by a session ?
    protected $set_cookie = true;   // Set a fresh cookie
    protected $raw = false;         // Display only the content ?
    protected $public = false;      // It's a public page
    protected $jsCheck = true;     // Check is Javascript is enabled
    protected $page;

    public function __construct()
    {
        $this->page = new Builder;
    }

    /**
     * Returns the value of a $_GET variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param  name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetchGet($name)
    {
        if (isset($_GET[$name])) {
            return htmlentities(urldecode($_GET[$name]));
        }

        return false;
    }

    /**
     * Returns the value of a $_POST variable. Mainly used to avoid getting
     * notices from PHP when attempting to fetch an empty variable.
     * @param  name is the desired variable's name.
     * @return the value of the requested variable, or FALSE.
     */
    protected function fetchPost($name)
    {
        if (isset($_POST[$name])) {
            return htmlentities($_POST[$name]);
        }

        return false;
    }

    protected function checkSession()
    {
        if ($this->session_only
        && !isLogged()) {
            $this->name = 'login';
        }
    }

    protected function redirect($page, $params = false)
    {
        $url = Route::urlize($page, $params);
        header('Location: '. $url);
        exit;
    }

    public function display()
    {
        $this->page->addCSS('style.css');
        $this->page->addCSS('notification.css');
        $this->page->addCSS('header.css');
        $this->page->addCSS('listn.css');
        $this->page->addCSS('grid.css');
        $this->page->addCSS('article.css');
        $this->page->addCSS('form.css');
        $this->page->addCSS('icon.css');
        $this->page->addCSS('dialog.css');
        $this->page->addCSS('card.css');
        $this->page->addCSS('chip.css');
        $this->page->addCSS('color.css');
        $this->page->addCSS('block.css');
        $this->page->addCSS('menu.css');
        $this->page->addCSS('fonts.css');
        $this->page->addCSS('title.css');
        $this->page->addCSS('typo.css');
        $this->page->addCSS('elevation.css');
        $this->page->addCSS('scrollbar.css');

        $this->page->addScript('movim_utils.js');
        $this->page->addScript('movim_events.js');
        $this->page->addScript('movim_jingles.js');
        $this->page->addScript('movim_e2ee.js');
        $this->page->addScript('movim_base.js');
        $this->page->addScript('movim_favicon.js');
        $this->page->addScript('movim_avatar.js');
        $this->page->addScript('movim_emojis_list.js');
        $this->page->addScript('movim_rpc.js');
        $this->page->addScript('movim_tpl.js');
        $this->page->addScript('libsignal_protocol.js');
        $this->page->addScript('thumbhash.js');

        if (!$this->public) {
            $this->page->addScript('movim_websocket.js');
        }

        $this->page->addScript('movim_visio.js');

        $content = new Builder;

        if ($this->jsCheck == false) {
            $content->disableJavascriptCheck();
        }

        $headers = getallheaders();

        $built = $content->build('common');
        $this->page->setCommonContent($built);

        if ($headers
         && array_key_exists('Accept', $headers)
         && array_key_exists('Content-Type', $headers)
         && $this->fetchGet('soft')
         && $headers['Accept'] == 'application/json'
         && $headers['Content-Type'] == 'application/json') {
            $built = $content->build($this->name);
            $this->page->setContent($built);

            header('Content-Type: application/json');
            echo json_encode($this->page->softBuild('page', $this->public));
        } elseif ($this->raw) {
            echo $content->build($this->name);
            exit;
        } else {
            $built = $content->build($this->name);
            $this->page->setContent($built);

            header('Strict-Transport-Security: max-age=31536000');
            header('Access-Control-Allow-Origin: *');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            echo $this->page->build('page', $this->public);
        }
    }

    public function load()
    {
    }

    public function dispatch()
    {
    }
}
