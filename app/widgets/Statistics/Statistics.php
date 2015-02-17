<?php

/**
 * @package Widgets
 *
 * @file Statistics.php
 * This file is part of MOVIM.
 *
 * @brief The administration widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 * *
 * Copyright (C)2014 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Modl\SessionxDAO;
 
class Statistics extends WidgetBase
{
    function load()
    {
    }

    function display()
    {
        $sd = new SessionxDAO;
        $this->view->assign('sessions',      $sd->getAll());

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        $this->view->assign('hash',          $config->password);

        $tmp = array();

        foreach(scandir(USERS_PATH) as $f) {
            if(is_dir(USERS_PATH.'/'.$f)) {
                $time = filemtime(USERS_PATH.'/'.$f.'/index.html');
                if($time) {
                    array_push($tmp, $time);
                }
            }
        }

        sort($tmp);
        $days = array();

        $pattern = "Y-m";
        
        foreach($tmp as $k => $time) {
            $key = date($pattern, $time);

            if(isset($days[$key])) {
                $days[$key]++;
            } else {
                $days[$key] = 1;
            }
        }

        $this->renderTimeLineChart($days, $this->__('statistics.monthly_sub'), "monthly.png");

        $sum = 0;
        foreach($days as $key => $value) {
            $sum = $sum + $value;
            $days[$key] = $sum;
        }
        
        $this->renderTimeLineChart($days, $this->__('statistics.monthly_sub_cum'), "monthly_cumulated.png");
    }

    public function getContact($username, $host)
    {
        $jid = $username.'@'.$host;
        $cd = new modl\ContactDAO;
        return $cd->get($jid);
    }

    private function renderTimeLineChart($data, $title, $filename)
    {
        $chart = new Libchart\View\Chart\LineChart(750, 450);

        $dataSet = new Libchart\Model\XYDataSet();

        foreach($data as $key => $value) {
            $dataSet->addPoint(new Libchart\Model\Point($key, $value));
        }

        $chart->setDataSet($dataSet);

        $chart->setTitle($title);
        $chart->render(CACHE_PATH.$filename);

        $this->view->assign('cache_path',      BASE_URI.'cache/');
    }

    public function ajaxGetSessions($hashs)
    {
        $sd = new SessionxDAO;
        $sessions = $sd->getAll();
    }

    function getTime($date)
    {
        return prepareDate(strtotime($date));
    }
}
