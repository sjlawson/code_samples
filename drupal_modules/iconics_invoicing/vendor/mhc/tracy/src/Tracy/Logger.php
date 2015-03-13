<?php

/**
 * This file is part of the Tracy (http://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Tracy;

use Tracy;


/**
 * Logger.
 *
 * @author     David Grudl
 */
class Logger
{
	const DEBUG = 'debug',
		INFO = 'info',
		WARNING = 'warning',
		ERROR = 'error',
		EXCEPTION = 'exception',
		CRITICAL = 'critical';

	/** @var int interval for sending email is 2 days */
	public $emailSnooze = 172800;

	/** @var callable handler for sending emails */
	public $mailer = array(__CLASS__, 'defaultMailer');

	/** @var string name of the directory where errors should be logged; FALSE means that logging is disabled */
	public $directory;

	/** @var string|array email or emails to which send error notifications */
	public $email;


	/**
	 * Logs message or exception to file and sends email notification.
	 * @param  string|array
	 * @param  int   one of constant Debugger::INFO, WARNING, ERROR (sends email), EXCEPTION (sends email), CRITICAL (sends email)
	 * @return void
	 */
	public function log($message, $priority = NULL)
	{
		if (is_array($message)) {
			$message = implode(' ', $message);
		}

		$willSendEmailPriorities = array(self::ERROR, self::EXCEPTION, self::CRITICAL);		
		if (in_array($priority, $willSendEmailPriorities, true)) {
			call_user_func(
				$this->mailer,
				$message,
				implode(', ', (array) $this->email)
			);
		}
	}

	/**
	 * Default mailer.
	 * @param  string
	 * @param  string
	 * @return void
	 * @internal
	 */
    public static function defaultMailer($message, $email)
    {
        $host = preg_replace('#[^\w.-]+#', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : php_uname('n'));

        mail(
            $email,
            Debugger::$moduleName . ": An error occurred on the server \"$host\"",
            $message,
            implode(PHP_EOL, self::getEmailHeaders())
        );
    }

    /**
     * Get the headers for emails.
     *
     * @param string $handler This is the handler that the email was sent from.
     * @return array Array of headers
     */
    static private function getEmailHeaders()
	{
        return array(
			'From: ' . Debugger::$moduleName . ' <www-data@mooreheadcomm.com>',
			'Date: ' . date('D, j M Y H:i:s O'),
			'MIME-Version: 1.0',
			'X-Mailer: PHP/' . phpversion(),
			'Content-Type: text/html; charset=ISO-8859-1'
        );
    }	
}
