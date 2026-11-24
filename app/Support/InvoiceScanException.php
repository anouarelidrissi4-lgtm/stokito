<?php

namespace App\Support;

use RuntimeException;

/** Scanner failure with a message safe to show; tokens already spent are kept for the usage log. */
class InvoiceScanException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null, public int $inputTokens = 0, public int $outputTokens = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}
