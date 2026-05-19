<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccionInmediata extends Model
{
    protected $table      = 'sgc_nc_acciones_inmediatas';
    protected $primaryKey = 'id';
    public $timestamps    = false;

    protected $fillable = ['id_nc', 'descripcion'];

    public function noConformidad()
    {
        return $this->belongsTo(NoConformidad::class, 'id_nc');
    }
}