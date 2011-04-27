<?php

/**
 * \brief Movim's custom exception class.
 *
 * Merely adds a line break to the messages so far. Is expected to become more
 * useful in the future.
 */
class MovimException extends Exception
{
	/**
	 * Forces to add a message.
	 */
	public function __construct($message, $code = 0) {
		parent::__construct(t("Error: %s", $message), $code);
	}

	/**
	 * Output proper html error reports.
	 */
	function __toString() {
		return $this->code . ' - ' . $this->message . '<br />';
	}
}

?>
