<?php

class Event_handler
{
	private $widgets = NULL;
	private $conf;

	function __construct()
	{
		$conf = new GetConf();
		
		$this->parse_tpl('main.tpl');
		//array_merge($widgets, $this->parse_tpl('page.tpl'));
	}

	private function parse_tpl($template)
	{
		$wids = array();
	
		preg_match_all('#\$this->widget\([\'"](.+?)[\'"]\);#',
					   file_get_contents(THEMES_PATH . $conf->getServerConfElement('theme') . '/' . $template),
					   $wids);

		$this->widgets = $wids[1];
	}

	function runEvent($type, $event)
	{
		foreach($widgets[1] as $widget) {
			$wid = new $widget();
			$wid->runEvents($type, $event);
		}
	}
}

?>