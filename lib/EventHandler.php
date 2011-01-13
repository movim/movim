<?php

class EventHandler
{
	private $widgets = NULL;
	private $conf;

	function __construct()
	{
		$this->conf = new GetConf();
		
		$this->parse_tpl('main.tpl');
		//array_merge($widgets, $this->parse_tpl('page.tpl'));
	}

	private function parse_tpl($template)
	{
		$wids = array();
	
		preg_match_all('#\$this->widget\([\'"](.+?)[\'"]\);#',
					   file_get_contents(THEMES_PATH . $this->conf->getServerConfElement('theme') . '/' . $template),
					   $wids);

		$this->widgets = $wids[1];
	}

	function runEvent($type, $event)
	{
		foreach($this->widgets as $widget) {
			$widget_path = "";
			if(file_exists(BASE_PATH . 'widgets/' . $widget . '/' . $widget . '.php')) {
				$widget_path = BASE_PATH . 'widgets/' . $widget . '/' . $widget . '.php';
				// Custom widgets have their own translations.
				load_extra_lang(BASE_PATH . 'widgets/' . $widget . '/i18n');
			}
			else if(file_exists(LIB_PATH . 'widgets/' . $widget . '.php')) {
				$widget_path = LIB_PATH . 'widgets/' . $widget . '.php';
			}
			else {
				throw new MovimException(
					sprintf(t("Error: Requested widget '%s' doesn't exist."), $widget));
			}
			
			$extern = false;
			$user = new User();
			require($widget_path);
			$wid = new $widget($extern, $user);
			$wid->runEvents($type, $event);
		}
	}
}

?>