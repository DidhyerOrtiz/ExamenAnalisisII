<?php

use App\Http\Controllers\Api\CambiarEstadoCitaController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PacienteController;
use Illuminate\Support\Facades\Route;

Route::get('/doctores', [DoctorController::class, 'index']);
Route::get('/pacientes', [PacienteController::class, 'index']);
Route::apiResource('citas', CitaController::class)->only(['index', 'store', 'show', 'update']);
Route::patch('/citas/{cita}/estado', CambiarEstadoCitaController::class);
