<?php

namespace App\Models\Model;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'guru_id',
        'hari',
        'jam_pelajaran',
    ];

    public function kelas()
    {
        return $this->belongsTo('App\Models\Model\Kelas', 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo('App\Models\Model\Mapel', 'mapel_id');
    }

    public function guru()
    {
        return $this->belongsTo('App\Models\Model\Guru', 'guru_id');
    }
}