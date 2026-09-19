<?php

namespace App\Exceptions;

use RuntimeException;

class HorarioNoDisponibleException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El doctor ya tiene una cita activa que se solapa con este horario.');
    }

    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
