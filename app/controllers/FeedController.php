<?php

class FeedController extends BaseController {
    function load() {
        $this->session_only = false;
        $this->raw = true;
    }

    function dispatch() {
    }
}
