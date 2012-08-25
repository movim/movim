<?php

/**
 * @file Controller.php
 * This file is part of MOVIM.
 *
 * @brief Implements a request handler that ensures application worklfow.
 *
 * @author Movim Project <movim@movim.eu>
 *
 * @version 1.0
 * @date 13 October 2010
 *
 * Copyright (C)2010 Movim Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class ControllerBase
{
	protected $default_handler = 'index';
	protected $token;

	public function __construct($token = 'q')
	{
        $this->load_language();
		$this->token = $token;
	}

	public function handle()
	{
		// Note that the request is always specified by 'q'.
		$request;
		if($request = $this->fetch_get('q')) {
			$this->run_req($request);
		} else {
			$this->run_req($this->default_handler);
		}
	}

    /**
     * Loads up the language, either from the User or default.
     */
    function load_language() {
        $user = new user();
		if($user->isLogged()) {
            try{
                $lang = $user->getConfig('language');//UserConf::getConf(false,'language');
                load_language($lang);
            }
            catch(MovimException $e) {
                // Load default language.
                load_language(Conf::getServerConfElement('defLang'));
            }
		}
		else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			load_language_auto();
		}
		else {
            load_language(Conf::getServerConfElement('defLang'));
        }
	}

	/**
	 * Attempts to call back the given function.
	 */
	protected function run_req($request)
	{
		if(is_callable(array($this, $request))) {
			call_user_func(array($this, $request));
		} else {
			$this->error404();
		}
	}

	/**
	 * Returns the value of a $_GET variable. Mainly used to avoid getting
	 * notices from PHP when attempting to fetch an empty variable.
	 * @param name is the desired variable's name.
	 * @return the value of the requested variable, or FALSE.
	 */
	protected function fetch_get($name)
	{
		if(isset($_GET[$name])) {
			return htmlentities($_GET[$name]);
		} else {
			return false;
		}
	}

	/**
	 * Returns the value of a $_POST variable. Mainly used to avoid getting
	 * notices from PHP when attempting to fetch an empty variable.
	 * @param name is the desired variable's name.
	 * @return the value of the requested variable, or FALSE.
	 */
	protected function fetch_post($name)
	{
		if(isset($_POST[$name])) {
			return htmlentities($_POST[$name]);
		} else {
			return false;
		}
	}

	/**
	 * Makes an error 404 page.
	 */
	protected function error404()
	{
		echo 'Error 404 - Page not found';
	}
}

?>
