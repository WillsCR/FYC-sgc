<?php

namespace App\Helpers;

use Carbon\Carbon;

class ControlInstrumentosHelper
{
    public static function obtenerFreqCalib($valor)
    {
        $frecuencias = [
            1 => 'Anual',
            2 => 'Mensual',
            3 => 'No aplica',
            4 => 'Semestral',
        ];

        return $frecuencias[$valor] ?? '-';
    }

    public static function obtenerFreqVerif($valor)
    {
        $frecuencias = [
            1 => 'Mensual',
        ];

        return $frecuencias[$valor] ?? '-';
    }

    public static function obtenerColorSemaforo($fecha)
    {
        if (!$fecha) {
            return '#dc3545'; // Rojo
        }

        $hoy = Carbon::now();
        $proxima = Carbon::parse($fecha);
        $diasRestantes = $hoy->diffInDays($proxima, false);

        if ($diasRestantes < 0) {
            return '#dc3545'; // Rojo - Vencido
        }

        if ($diasRestantes < 30) {
            return '#ffc107'; // Amarillo - Por vencer
        }

        return '#28a745'; // Verde - Vigente
    }

    public static function obtenerClaseVigencia($fecha)
    {
        if (!$fecha) {
            return 'text-danger';
        }

        $hoy = Carbon::now();
        $proxima = Carbon::parse($fecha);
        $diasRestantes = $hoy->diffInDays($proxima, false);

        if ($diasRestantes < 0) {
            return 'text-danger font-weight-bold';
        }

        if ($diasRestantes < 30) {
            return 'text-warning font-weight-bold';
        }

        return 'text-success';
    }

    public static function obtenerEstadoVigencia($fecha)
    {
        if (!$fecha) {
            return 'Sin vigencia';
        }

        $hoy = Carbon::now();
        $proxima = Carbon::parse($fecha);
        $diasRestantes = $hoy->diffInDays($proxima, false);

        if ($diasRestantes < 0) {
            return 'Vencido';
        }

        if ($diasRestantes < 30) {
            return "Por vencer ({$diasRestantes} días)";
        }

        return 'Vigente';
    }
}
