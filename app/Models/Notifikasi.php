<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'user_id',
        'sender_id',
        'judul',
        'pesan',
        'url',
        'tipe',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id_user');
    }
}
