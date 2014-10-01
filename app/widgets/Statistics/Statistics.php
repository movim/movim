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
 
class Statistics extends WidgetBase {
    function load() {
        $sd = new SessionxDAO;
        $this->view->assign('sessions',      $sd->getAll());

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

        $this->renderTimeLineChart($days, "Monthly Subscriptions", "monthly.png");

        $sum = 0;
        foreach($days as $key => $value) {
            $sum = $sum + $value;
            $days[$key] = $sum;
        }
        
        $this->renderTimeLineChart($days, "Monthly Subscriptions Cumulated", "monthly_cumulated.png");
    }

    private function renderTimeLineChart($data, $title, $filename) {
        $chart = new Libchart\View\Chart\LineChart(700, 450);

        $dataSet = new Libchart\Model\XYDataSet();

        foreach($data as $key => $value) {
            $dataSet->addPoint(new Libchart\Model\Point($key, $value));
        }

        $chart->setDataSet($dataSet);

        $chart->setTitle($title);
        $chart->render(CACHE_PATH.$filename);

        $this->view->assign('cache_path',      BASE_URI.'cache/');
    }

    function getTime($date) {
        return prepareDate(strtotime($date));
    }
}
