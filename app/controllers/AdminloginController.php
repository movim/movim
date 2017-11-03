<?php
use Movim\Controller\Base;

class AdminloginController extends Base
{
    private static function getHashedPassword($password)
    {
        return sha1($password);
    }

    private static function isCorrectPassword($exceptedPassword, $givenPassword)
    {
        return $exceptedPassword == self::getHashedPassword($givenPassword);
    }

    function load()
    {
        $this->session_only = false;
    }

    function dispatch()
    {
        $this->page->setTitle(__('page.administration'));

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if(isset($_POST['username'])
        && $config->username == $_POST['username']
        && self::isCorrectPassword($config->password, $_POST['password'])) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
