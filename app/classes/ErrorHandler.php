<?php

namespace App\classes;

class ErrorHandler {

	protected $logFile;

	public function __construct($logFileDir = NULL, $logFile = NULL)
	{
		try {
			$logFile    = $logFile    ?? date('Ymd') . '.log';
			$logFileDir = $logFileDir ?? __DIR__;
			$this->logFile = $logFileDir . '/' . $logFile;
			$this->logFile = str_replace('//', '/', $this->logFile);
			set_exception_handler([$this,'exceptionHandler']);
			set_error_handler([$this, 'handleErrors']);
		} catch (\Throwable $th) {
			$th->getMessage().'\n';
		}		
	}
	
	public function handleErrors ($errorNumber, $errorMessage, $errorFile, $errorLine)
	{
		// $error = "ERROR: [{$errorNumber}] An error occurred in file {$errorFile} on line {$errorLine}: $errorMessage";
		$environment = getenv('APP_ENV');
		$message = sprintf('ERROR    : %s : %d : %s : %s : %s' . PHP_EOL,
			date('Y-m-d H:i:s'), $errorNumber, $errorMessage, $errorFile, $errorLine);

		file_put_contents($this->logFile, $message, FILE_APPEND);

		if ($environment === 'local') {
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		
		} else {
				$subject = "There is an error on $errorFile";
				// send_email_self($subject, $message);
				// log the error
				$error = ['error' => $message];
				view('errors/errorGen', compact('error'));
	
			}
	}

	
	public function exceptionHandler($ex)
	{	
		$message = sprintf('EXCEPTION: %19s : %20s : %s' . PHP_EOL,
				date('Y-m-d H:i:s'), get_class($ex), $ex->getMessage());
		file_put_contents($this->logFile, $message, FILE_APPEND);
		$environment = getenv('APP_ENV');

		if ($environment === 'local') {
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		} else {
			$subject = "There is an error on ". get_class($ex);			
			// send_email_self($subject, $message);
			$error = ['error' => $ex->getMessage()];
			view('errors/errorGen', compact('error'));
			
		}
	}

}
