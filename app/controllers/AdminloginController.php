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

    private static function isCorrectCredentials($config, $credentialsGiven)
    {
        return (
           isset($credentialsGiven['username'])
        && isset($credentialsGiven['password'])
        && $config->username == $credentialsGiven['username']
        && self::isCorrectPassword($config->password, $credentialsGiven['password'])
        );
    }

    private static function isCorrectCredentialsFromPost($config)
    {
        return self::isCorrectCredentials($config, $_POST);
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

        if(self::isCorrectCredentialsFromPost($config)) {
            $_SESSION['admin'] = true;
            $this->name = 'admin';
        }
    }
}
