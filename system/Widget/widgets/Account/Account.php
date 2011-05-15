<?php

/**
 * @file Chat.php
 * This file is part of MOVIM.
 * 
 * @brief The account creation
 *
 * @author Timothée Jaussoin <edhelas_at_g m a i l dot com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Account extends WidgetBase
{
	private $user;
	
	function __construct(&$user)
	{
		$this->user = $user;
	}
	
	function build()
	{

	}
}

?>
