<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoConformidadDoc extends Model
{
    protected $table = 'ges_no_conformidades_docs';
    public $timestamps = false;

    protected $fillable = [
        'id_nc',
        'documento',
        'ruta',
    ];

    public function noConformidad()
    {
        return $this->belongsTo(NoConformidad::class, 'id_nc');
    }
}
