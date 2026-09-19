<?php

namespace App\Exceptions;

use RuntimeException;

class OperacionCitaInvalidaException extends RuntimeException
{
    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
