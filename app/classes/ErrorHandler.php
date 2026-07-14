<?php

declare(strict_types=1);

namespace App\classes;

class ErrorHandler
{

	protected mixed $logFile;
	private string $env;



	public function __construct()
	{
		try {

			$this->env = getenv('APP_ENV');

			$this->logFile =  __DIR__ . '/../../bootstrap/log/' .  date('Ymd') . '.log';
			$this->logFile = str_replace('//', '/', $this->logFile);

			set_exception_handler([$this, 'exceptionHandler']);
			set_error_handler([$this, 'handleErrors']);
		} catch (\Throwable $th) {
			\showError($th);
		}
	}


	public function handleErrors($errorNumber, $errorMessage, $errorFile, $errorLine)
	{
		// $error = "ERROR: [{$errorNumber}] An error occurred in file {$errorFile} on line {$errorLine}: $errorMessage";

		$message = sprintf(
			'ERROR    : %s : %d : %s : %s : %s' . PHP_EOL,
			date('Y-m-d H:i:s'),
			$errorNumber,
			$errorMessage,
			$errorFile,
			$errorLine
		);
		$message .= " | URL: {$_SERVER['REQUEST_URI']} | User ID: " . ($_SESSION['ID'] ?? 'N/A');

		file_put_contents($this->logFile, $message, FILE_APPEND);

		if ($this->env === 'local') {
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		} else {
			$subject = "There is an error on $errorFile";
			send_email_self($subject, $message);

			$error = ['error' =>  $errorMessage];
			view('error/genError', compact('error'));
		}
	}


	public function exceptionHandler($ex)
	{
		$message = sprintf(
			'EXCEPTION: %19s : %20s : %s' . PHP_EOL,
			date('Y-m-d H:i:s'),
			get_class($ex),
			$ex->getMessage()
		);
		$message .= " | URL: {$_SERVER['REQUEST_URI']} | User ID: " . ($_SESSION['ID'] ?? 'N/A');
		file_put_contents($this->logFile, $message, FILE_APPEND);

		if ($this->env === 'local') {
			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		} else {
			$subject = "There is an error on " . get_class($ex);
			send_email_self($subject, $message);
			$error = ['error' => $ex->getMessage()];
			view('error/genError', compact('error'));
		}
	}
}
