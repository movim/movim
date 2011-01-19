<?php

class EventHandler
{
	private $widgets = NULL;
	private $conf;

	function __construct()
	{
		$this->conf = new GetConf();
		
		$this->getLoadedWidgets();
	}
	
	public function setLoadedWidgets($loaded_widgets) 
	{
		$_SESSION['loaded_widgets'] = $loaded_widgets;
	}
	
	public function getLoadedWidgets() 
	{
		$this->widgets = $_SESSION['loaded_widgets'];
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

			if($type == 'vcardreceived') {
				echo "vcard received. Runnung $widget hooks.<br />\n";
			}
			
			$extern = false;
			$user = new User();
			require_once($widget_path);
			$wid = new $widget($extern, $user);
			$wid->runEvents($type, $event);
		}
	}
}

?>
