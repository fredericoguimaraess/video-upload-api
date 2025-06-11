<?php

namespace App\Exceptions;

use Exception;

class VideoProcessingException extends Exception
{
    public function __construct(string $message = "Erro no processamento do vídeo", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
