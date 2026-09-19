<?php

namespace App\Enums;

enum EstadoCita: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
    case Atendida = 'atendida';

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => '#d97706',
            self::Confirmada => '#0f766e',
            self::Cancelada => '#dc2626',
            self::Atendida => '#4f46e5',
        };
    }
}
