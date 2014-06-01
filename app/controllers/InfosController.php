<?php

class InfosController extends BaseController {
    function load() {
        header('Content-type: application/json');
        $this->session_only = false;
        $this->raw = true;
    }

    function dispatch() {
    }
}
