<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace Movim\Template;

use App\Configuration;
use App\User;
use Movim\Controller\Ajax;
use Movim\Widget\Wrapper;
use stdClass;

class Builder
{
    private string $_view = '';
    private string $title = APP_TITLE;
    private string $content = '';
    private string $commonContent = '';
    private array $css = [];
    private array $scripts = [];
    private string $eagerScripts = "/\/(movim_rpc|movim_utils)/";
    private string $dir = 'ltr';
    private bool $public;
    private ?User $user = null;
    private $jsCheck = true;

    /**
     * Constructor. Determines whether to show the login page to the user or the
     * Movim interface.
     */
    public function __construct()
    {
        if (isLogged()) {
            $this->user = User::me();
        }
    }

    public function viewsPath(string $file)
    {
        return VIEWS_PATH . '/' . $file;
    }

    /**
     * Returns or prints the link to a file.
     * @param file is the path to the file
     * @param return optionally returns the link instead of printing it if set to true
     */
    public function linkFile(string $file, $return = false)
    {
        $path = urilize('theme/' . $file);

        if ($return) {
            return $path;
        }

        echo $path;
    }

    /**
     * Actually generates the page from templates.
     */
    public function build(string $view, bool $public = false)
    {
        $this->_view = $view;
        $this->public = $public;
        $template = $this->_view.'.tpl';

        ob_start();

        require($this->viewsPath($template));
        $outp = ob_get_clean();

        $scripts = $this->printCSSs();
        $scripts .= $this->printScripts();

        $outp = str_replace(
            ['<%scripts%>', '<%meta%>', '<%content%>', '<%common%>', '<%title%>', '<%language%>', '<%dir%>'],
            [$scripts, $this->meta(), $this->content, $this->commonContent, $this->title(), $this->language(), $this->dir()],
            $outp
        );

        return $outp;
    }

    /**
     * Generate the page
     */
    public function softBuild(string $view, bool $public = false): stdClass
    {
        $this->_view = $view;
        $this->public = $public;

        $page = new stdClass;

        $page->title = $this->title();

        $widgets = Wrapper::getInstance();
        $page->widgetsCSS = $widgets->loadcss();
        $page->widgetsScripts = $widgets->loadjs();
        $ajaxer = Ajax::getInstance();
        $page->inlineScripts = $ajaxer->genJsContent();

        $page->content = $this->content;

        return $page;
    }

    /**
     * Sets the page's title
     */
    public function setTitle(string $name)
    {
        $this->title = $name;
    }

    /**
     * Disable Javascript check
     */
    public function disableJavascriptCheck()
    {
        $this->jsCheck = false;
    }

    /**
     * Displays the current title
     */
    public function title(): string
    {
        $widgets = Wrapper::getInstance();

        return isset($widgets->title)
            ? $this->title . ' • ' . $widgets->title
            : $this->title;
    }

    /**
     * Displays the current language
     */
    public function language(): string
    {
        $lang = $this->user && $this->user->language
            ? $this->user->language
            : Configuration::get()->locale;

        if (empty($lang)) {
            return 'en';
        }

        // String casing for the web
        $split = array_slice(preg_split('/[_-]/', $lang), 0, 3);
        $lc = array_shift($split);
        $langCode = strtolower($lc);

        if (empty($split)) {
           return $langCode;
        }

        $tags = array_map(function ($tag) {
            switch (strlen($tag)) {
                // Region
                case 2:
                case 3:
                   return strtoupper($tag);
                // Script
                case 4:
                   return ucfirst(strtolower($tag));
                default:
                   return $tag;
            }
        }, $split);

        array_unshift($tags, $langCode);

        return implode('-', $tags);
    }

    /**
     * Displays the current font direction
     */
    public function dir()
    {
        if (isLogged()) {
            $lang = \App\User::me()->language;

            if (in_array($lang, ['ar', 'he', 'fa'])) {
                $this->dir = 'rtl';
            }
        }

        return $this->dir;
    }

    /**
     * Display some meta tag defined in the widgets using Facebook OpenGraph
     */
    public function meta(): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $metas = $dom->createElement('xml');
        $dom->appendChild($metas);

        $widgets = Wrapper::getInstance();

        $title = $this->title;

        if (isset($widgets->title)) {
            $title .= ' • ' . $widgets->title;
        }

        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', 'og:title');
        $meta->setAttribute('content', $title);
        $metas->appendChild($meta);

        $meta = $dom->createElement('meta');
        $meta->setAttribute('name', 'twitter:title');
        $meta->setAttribute('content', $title);
        $metas->appendChild($meta);

        if (isset($widgets->image)) {
            $meta = $dom->createElement('meta');
            $meta->setAttribute('property', 'og:image');
            $meta->setAttribute('content', $widgets->image);
            $metas->appendChild($meta);

            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'twitter:image');
            $meta->setAttribute('content', $widgets->image);
            $metas->appendChild($meta);
        }

        if (isset($widgets->description) && !empty($widgets->description)) {
            $widgets->description = truncate(stripTags($widgets->description), 100);

            $meta = $dom->createElement('meta');
            $meta->setAttribute('property', 'og:description');
            $meta->setAttribute('content', $widgets->description);
            $metas->appendChild($meta);

            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'twitter:description');
            $meta->setAttribute('content', $widgets->description);
            $metas->appendChild($meta);

            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'description');
            $meta->setAttribute('content', $widgets->description);
            $metas->appendChild($meta);
        } else if (!empty(Configuration::get()->description)) {
            $meta = $dom->createElement('meta');
            $meta->setAttribute('name', 'description');
            $meta->setAttribute('content', Configuration::get()->description);
            $metas->appendChild($meta);
        }

        if (isset($widgets->url)) {
            $meta = $dom->createElement('meta');
            $meta->setAttribute('property', 'og:url');
            $meta->setAttribute('content', $widgets->url);
            $metas->appendChild($meta);
        }

        if (isset($widgets->links)) {
            foreach ($widgets->links as $l) {
                $link = $dom->createElement('link');
                $link->setAttribute('rel', $l['rel']);
                $link->setAttribute('type', $l['type']);
                $link->setAttribute('href', $l['href']);
                $metas->appendChild($link);
            }
        }

        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', 'og:type');
        $meta->setAttribute('content', 'article');
        $metas->appendChild($meta);

        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', 'twitter:card');
        $meta->setAttribute('content', 'summary');
        $metas->appendChild($meta);

        $meta = $dom->createElement('meta');
        $meta->setAttribute('property', 'twitter:site');
        $meta->setAttribute('content', 'MovimNetwork');
        $metas->appendChild($meta);

        return strip_tags($dom->saveXML($dom->documentElement), '<meta><link>');
    }

    public function addScript(string $script)
    {
        $this->scripts[] = urilize('scripts/' . $script);
    }

    public function addCSS(string $file)
    {
        $this->css[] = $this->linkFile('css/' . $file, true);
    }

    public function setContent(string $data)
    {
        $this->content = $data;
    }

    public function setCommonContent(string $data)
    {
        $this->commonContent = $data;
    }

    private function printScripts(): string
    {
        $out = '';
        $widgets = Wrapper::getInstance();

        foreach ($this->scripts as $script) {
            $out .= $this->printScript($script, 'page');
        }

        foreach ($widgets->loadjs() as $script) {
            $out .= $this->printScript($script, 'widget');
        }

        $ajaxer = Ajax::getInstance();
        $out .= $ajaxer->genJs();

        return $out;
    }

    private function printScript(string $script, string $class = ''): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $s = $dom->createElement('script');
        $s->setAttribute('type', 'text/javascript');
        $s->setAttribute('src', $script);

        if (!preg_match($this->eagerScripts, $script)) {
            $s->setAttribute('defer', '');
        }

        if (!empty($class)) {
            $s->setAttribute('class', $class);
        }

        $dom->appendChild($s);

        return $dom->saveHTML($dom->documentElement);
    }

    private function printCSSs(): string
    {
        $out = '';
        $widgets = Wrapper::getInstance();

        foreach ($this->css as $css) {
            $out .= $this->printCSS($css, 'page');
        }

        foreach ($widgets->loadcss() as $css) {
            $out .= $this->printCSS($css, 'widget');
        }

        return $out;
    }

    private function printCSS(string $css, string $class = ''): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $s = $dom->createElement('link');
        $s->setAttribute('rel', 'stylesheet');
        $s->setAttribute('href', $css);

        if (!empty($class)) {
            $s->setAttribute('class', $class);
        }

        $dom->appendChild($s);

        return $dom->saveHTML($dom->documentElement);
    }

    /**
     * Loads up a widget and prints it at the current place.
     */
    public function widget(string $name)
    {
        $widgets = Wrapper::getInstance();
        $widgets->setView($this->_view);

        echo $widgets->runWidget($name, 'build');
    }
}
