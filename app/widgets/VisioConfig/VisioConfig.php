<?php

use Movim\Widget\Base;

class VisioConfig extends Base
{
    public function load()
    {
        $this->addjs('visioconfig.js');
        $this->addcss('visioconfig.css');
    }

    public function ajaxDefaultMicrophoneChanged()
    {
        Toast::send($this->__('visioconfig.default_microphone_changed'));
    }

    public function ajaxDefaultCameraChanged()
    {
        Toast::send($this->__('visioconfig.default_camera_changed'));
    }
}
