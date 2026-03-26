<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccionInmediataResp extends Model
{
    protected $table = 'ges_acciones_inmediatas_resp';
    public $timestamps = false;
    protected $fillable = ['id_accion', 'id_usuario', 'responsable'];
}

// app/Models/AccionCorrectivaResp.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccionCorrectivaResp extends Model
{
    protected $table = 'ges_acciones_correctivas_resp';
    public $timestamps = false;
    protected $fillable = ['id_accion', 'id_usuario', 'responsable'];
}

// app/Models/NoConformidadDoc.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NoConformidadDoc extends Model
{
    protected $table = 'ges_no_conformidades_docs';
    public $timestamps = false;
    protected $fillable = ['id_nc', 'documento', 'ruta'];
}

// app/Models/MinutaConvocado.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MinutaConvocado extends Model
{
    protected $table = 'sgc_minutas_convocados';
    public $timestamps = false;
    protected $fillable = ['id_minuta', 'id_usuario', 'asistencia'];
}

// app/Models/Video.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'sgc_videos';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['titulo', 'url', 'descripcion', 'fecha_carga'];
}

// app/Models/ProgramaVerificacion.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgramaVerificacion extends Model
{
    protected $table = 'sgc_programa_verificacion';
    public $timestamps = false;
    protected $fillable = ['id_equipo', 'tipo_verificacion', 'fecha_programada', 'fecha_realizada', 'estado'];
}