<?php
namespace Movim\Task;

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;

class CheckSmallPicture extends Engine {

    private $_client;

    public function __construct()
    {
        $engine = parent::start();
        $browser = new Browser($engine->_loop);

        $this->_client = $browser->withOptions([
            'timeout' => 2
        ]);
    }

    public function run($url)
    {
        return $this->_client->head($url)
            ->then(function (ResponseInterface $response) {
                $length = $response->getHeader('content-length');

                $size = 300000;

                if($length) {
                    $length = (int)$length[0];
                    $type   = (string)$response->getHeader('content-type')[0];
                    $typearr = explode('/', $type);
                    return ($typearr[0] == 'image'
                        && $length <= $size
                        && $length >= 5000);
                }

                return false;
            },
            function (\Exception $error) {
                return false;
            })
            ->always(function() { writeOut(); });
    }
}
