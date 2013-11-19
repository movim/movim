<?php

class VisioController extends BaseController {
    function load() {
        $this->session_only = true;
        $this->raw = true;
    }

    function dispatch() {
    }
}
