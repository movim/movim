<?php 		
class Parser
{
	function __construct ()
	{
		require_once LIB_PATH.'MagpieRSS/rss_fetch.inc';
	}
	
	function fetch($url)
	{
		return fetch_rss($url);
	}

}
