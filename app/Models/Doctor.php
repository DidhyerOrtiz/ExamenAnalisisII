<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctores';

    protected $fillable = ['nombre', 'especialidad', 'numero_colegiado', 'email'];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
