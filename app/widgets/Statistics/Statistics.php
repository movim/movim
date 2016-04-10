<?php

use Modl\SessionxDAO;

class Statistics extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('chart.min.js');
        $this->addjs('statistics.js');
    }

    function ajaxDisplay()
    {
        $tmp = [];

        foreach(scandir(USERS_PATH) as $f) {
            if(is_dir(USERS_PATH.'/'.$f)) {
                $time = filemtime(USERS_PATH.'/'.$f.'/index.html');
                if($time) {
                    array_push($tmp, $time);
                }
            }
        }

        sort($tmp);
        $days = [];

        $pattern = "M Y";

        foreach($tmp as $k => $time) {
            $key = date($pattern, $time);

            if(isset($days[$key])) {
                $days[$key]++;
            } else {
                $days[$key] = 1;
            }
        }

        $data = new stdClass;
        $data->labels = [];
        $data->datasets = [];

        $first = new StdClass;
        $first->label = "Monthly Subscriptions";
        $first->fillColor = "rgba(255,152,0,0.5)";
        $first->strokeColor = "rgba(255,152,0,0.8)";
        $first->highlightFill = "rgba(220,220,220,0.75)";
        $first->highlightStroke = "rgba(220,220,220,1)";

        $values = [];
        foreach($days as $key => $value) {
            array_push($data->labels, $key);
            array_push($values, $value);
        }

        $first->data = $values;

        array_push($data->datasets, $first);

        RPC::call('Statistics.drawGraphs', $data);
    }

    function display()
    {
        $sd = new SessionxDAO;
        $this->view->assign('sessions',      $sd->getAll());
    }

    public function getContact($username, $host)
    {
        $jid = $username.'@'.$host;
        $cd = new modl\ContactDAO;
        return $cd->get($jid);
    }

    function getTime($date)
    {
        return prepareDate(strtotime($date));
    }
}
