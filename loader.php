<?php

// Loads up all system libraries.

require(LIB_PATH . "Lang/i18n.php");
require(LIB_PATH . "Utils.php");
require(LIB_PATH . "Cache.php");
require(LIB_PATH . "Conf.php");
require(LIB_PATH . "Event.php");
require(LIB_PATH . "Form.php");
require(LIB_PATH . "Jabber.php");
require(LIB_PATH . "MovimException.php");
require(LIB_PATH . "RPC.php");
require(LIB_PATH . "User.php");

require(LIB_PATH . "Controller/ControllerBase.php");
require(LIB_PATH . "Controller/ControllerMain.php");
require(LIB_PATH . "Controller/ControllerAjax.php");

require(LIB_PATH . "Tpl/TplPageBuilder.php");

require(LIB_PATH . "Widget/WidgetBase.php");
require(LIB_PATH . "Widget/WidgetWrapper.php");

?>