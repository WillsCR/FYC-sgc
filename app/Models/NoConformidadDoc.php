<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoConformidadDoc extends Model
{
    protected $table      = 'sgc_nc_documentos';
    public $timestamps    = false;
    const CREATED_AT      = 'creada_el';

    protected $fillable = [
        'id_nc', 'archivo', 'nombre_original', 'tipo', 'id_usuario',
    ];

    public function noConformidad()
    {
        return $this->belongsTo(NoConformidad::class, 'id_nc');
    }
}
