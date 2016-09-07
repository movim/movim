<?php

class AdminMain extends \Movim\Widget\Base
{
    function load() {
        $this->addjs('admin.js');

        $form = $_POST;
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if(isset($form) && !empty($form)) {
            if(isset($form['password'])
            && $form['password'] != '' && $form['repassword'] != ''
            && $form['password'] == $form['repassword']) {
                $form['password'] = sha1($form['password']);
            } else {
                $form['password'] = $config->password;
            }

            unset($form['repassword']);

            foreach($form as $key => $value) {
                $config->$key = $value;
            }

            $cd->set($config);

            //set timezone
            if(isset($form['timezone'])) {
                date_default_timezone_set($form['timezone']);
            }
        }
    }

    public function testBosh($url)
    {
        return requestURL($url, 1);
    }

    public function date($timezone)
    {
        $t = new DateTimeZone($timezone);
        $c = new DateTime(null, $t);
        $current_time = $c->format('D M j Y G:i:s');
        return $current_time;
    }

    function display()
    {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $l = Movim\i18n\Locale::start();

        $this->view->assign('conf', $cd->get());
        $this->view->assign('logs',
            array(
                0 => $this->__('log.empty'),
                1 => $this->__('log.syslog'),
                2 => $this->__('log.syslog_files'))
        );

        $this->view->assign('bosh_info4',
            $this->__('bosh.info4', '<a href="http://wiki.movim.eu/en:install">', '</a>'));

        $json = requestURL(MOVIM_API.'websockets', 3);
        $json = json_decode($json);

        if(isset($json) && $json->status != 404) {
            $this->view->assign('websockets', $json);
        }

        $this->view->assign('timezones', getTimezoneList());
        $this->view->assign('langs', $l->getList());
        $this->view->assign('countries', getCountries());
    }
}
