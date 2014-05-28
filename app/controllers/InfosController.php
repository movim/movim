<?php

class InfosController extends BaseController {
    function load() {
        $this->session_only = false;
        $this->raw = true;
    }

    function dispatch() {
    }
}
