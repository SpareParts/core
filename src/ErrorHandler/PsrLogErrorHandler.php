<?php declare(strict_types = 1);

namespace Apitte\Core\ErrorHandler;

use Apitte\Core\Exception\ApiException;
use Apitte\Core\Exception\Runtime\SnapshotException;
use Apitte\Core\Http\ApiResponse;
use Psr\Log\LoggerInterface;
use Throwable;

class PsrLogErrorHandler extends SimpleErrorHandler
{

	/** @var LoggerInterface */
	private $logger;

	public function __construct(ErrorConverter $errorConverter, LoggerInterface $logger)
	{
		parent::__construct($errorConverter);
		$this->logger = $logger;
	}

	public function handle(Throwable $error): ApiResponse
	{
		// Pass to SimpleErrorHandler same exception which would get without logger wrapper
		$originalError = $error;

		// Unwrap exception
		if ($error instanceof SnapshotException) {
			$error = $error->getPrevious();
		}

		// Log exception only if it's not designed to be displayed
		if (!($error instanceof ApiException)) {
			$this->logger->error($error->getMessage(), ['exception' => $error]);
		}

		// Also log original exception if any
		if ($error instanceof ApiException && ($previous = $error->getPrevious()) !== null) {
			$this->logger->error($previous->getMessage(), ['exception' => $previous]);
		}

		return parent::handle($originalError);
	}

}
