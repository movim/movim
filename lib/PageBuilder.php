<?php 

//doc
//	classname:	PageBuilder
//	scope:		PUBLIC
//
///doc

/**
 * \class PageBuilder
n * \brief Templating engine for Movim
 *
 * This class is the templating engine for movim. It determines what page to
 * load based on the context and fills in placeholder values ('%' delimited).
 *
 * It also handles themes.
 */
class PageBuilder 
{
	//	internal variables
	private $theme = 'movim';
	private $title = '';
	private $menu = array();
	private static $scripts;
	private static $css;
	private $content = '';
	private $user;
	
	/**
	 * Constructor. Determines whether to show the login page to the user or the
	 * Movim interface.
	 */
	function __construct(&$user = NULL)
	{
		$this->user = $user;
		$conf = new GetConf();
		$this->theme = $conf->GetConfElement('theme');

		if(!is_array(self::$scripts)) {
			self::$scripts = array();
		}
		if(!is_array(self::$css)) {
			self::$css = array();
		}
	}
	
	function theme_path($file)
	{
		return THEMES_PATH . $this->theme . '/' . $file;
	}

	/**
	 * Returns or prints the link to a file.
	 * @param file is the path to the file relative to the theme's root
	 * @param return optionally returns the link instead of printing it if set to true
	 */
	function link_file($file, $return = false)
	{
		$path = BASE_URI . 'themes/' . $this->theme . '/' . $file;
		
		if($return) {
			return $path;
		} else {
			echo $path;
		}
	}

	/**
	 * Inserts the link tag for a css file.
	 */
	function theme_css($file)
	{
		echo '<link rel="stylesheet" href="'
			. $this->link_file($file, true) .
			"\" type=\"text/css\" />\n";
	}
	
	/**
	 * Inserts the link tag for a theme picture
	 */
	function theme_img($src, $alt)
	{
		$size = getimagesize($this->link_file($src, true));
		$outp = '<img src="'
			. $this->link_file($src, true) . '" '
			. $size["3"];
		
		if(!empty($alt)) {
			$outp .=' alt="'.$alt.'"';
		}
		$outp .='>';
		
		return $outp;
	}

	/**
	 * Actually generates the page from templates.
	 */
	function build($template)
	{
		ob_start();
		require($this->theme_path($template));
		$outp = ob_get_clean();
		$outp = str_replace('<%scripts%>',
							$this->printCss() . $this->printScripts(),
							$outp);
		return $outp;
	}

	/**
	 * Sets the page's title.
	 */
	function setTitle($name)
	{
		$this->title = $name;
	}

	/**
	 * Displays the current title.
	 */
	function title()
	{
		echo $this->title;
	}
	
	/**
	 * Adds a link to the menu with the displayed label.
	 */
	function menuAddLink($label, $href)
	{
		$this->menu[] = array(
			'type' => 'link',
			'label' => $label,
			'href' => $href,
			);
	}

	function menuAddVerbatim($html)
	{
		$this->menu[] = array(
			'type' => 'verbatim',
			'html' => $html,
			);
	}

	/** shows up the menu. */
	function menu()
	{
		echo '<ul class="menu">' . "\n";
		foreach($this->menu as $link) {
			if($link['type'] == 'link') {
				echo "\t\t".'<li><a href="' . $link['href'] . '">'
					. $link['label'] . "</a></li>\n";
			} else {
				echo $link['html'];
			}
		}
		echo "\t</ul>\n";
	}

	function addScript($script)
	{
		self::$scripts[] = BASE_URI . 'js/' . $script;
	}

	/**
	 * Inserts the link tag for a css file.
	 */
	function addCss($file)
	{
		self::$css[] = $this->link_file($file, true);
	}

	function scripts()
	{
		echo '<%scripts%>';
	}

	function printScripts() {
		$out = '';
		foreach(self::$scripts as $script) {
			 $out .= '<script type="text/javascript" src="'
				 . $script .
				 '"></script>'."\n";
		}

		$ajaxer = Ajaxer::getInstance();
		$out .= $ajaxer->genJs();
		
		return $out;
	}

	function printCss() {
		$out = '';
		foreach(self::$css as $css_path) {
			$out .= '<link rel="stylesheet" href="'
				. $css_path .
				"\" type=\"text/css\" />\n";
		}
		return $out;
	}

	function setContent($data)
	{
		$this->content = $data;
	}

	function addContent($data, $append = true)
	{
		if($append) {
			$this->content .= $data;
		} else {
			$this->content = $data . $this->content;
		}
	}

	function content()
	{
		echo $this->content;
	}

	/**
	 * Loads up a widget and prints it at the current place.
	 */
	function widget($name)
	{
		$widget_path = LIB_PATH . 'widgets/' . $name . '.php';
		if(file_exists($widget_path)) {
			require($widget_path);
			$extern = false;
			$widget = new $name($extern, $this->user);
			$widget->build();
			
			self::$css = array_merge(self::$css, $widget->loadcss());
			self::$scripts = array_merge(self::$scripts, $widget->loadjs());
		} else {
			throw new MovimException(
				sprintf(_("Error: Requested widget '%s' doesn't exist."), $name));
		}
	}
}

?>
