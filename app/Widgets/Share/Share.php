<?php

namespace App\Widgets\Share;

use Movim\Route;
use Movim\Session;
use Movim\Widget\Base;
use Movim\XMPPUri;
use Respect\Validation\Validator;

class Share extends Base
{
    public function load()
    {
        $this->addjs('share.js');
    }

    public function ajaxGet($link)
    {
        $validateUrl = Validator::url();

        if (
            $validateUrl->isValid($link)
            && substr($link, 0, 4) == 'http'
        ) {
            $session = Session::instance();
            $session->set('share_url', $link);

            $this->rpc('MovimUtils.redirect', $this->route('publish'));
        } else {
            $uri = new XMPPUri($link);
            $route = $uri->getRoute();

            if ($route) {
                $this->rpc('MovimUtils.redirect', $route);
            }
        }
    }

    public function display()
    {
        // https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Manifest/Reference/share_target#receiving_share_data_using_post
        $url = null;
        $validateUrl = Validator::url();

        if (array_key_exists('url', $_POST) && $validateUrl->isValid($_POST['url'])) {
            $url = $_POST['url'];
        } else if (array_key_exists('description', $_POST) && $validateUrl->isValid($_POST['description'])) {
            $url = $_POST['description'];
        }

        if ($url) {
            $url = Route::urlize('share', base64_encode($url));
            header('Location: ' . $url);
            exit;
        }
    }
}
