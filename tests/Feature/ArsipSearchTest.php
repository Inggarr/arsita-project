<?php

namespace Tests\Feature;

use App\Models\KategoriSurat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArsipSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_arsip_search_with_empty_date_filters_does_not_throw_exception(): void
    {
        $role = Role::create(['id_role' => 1, 'nama_role' => 'admin_rektorat']);
        $user = User::create([
            'id_user' => 1,
            'nama' => 'Admin Test',
            'username' => 'admintest',
            'password' => bcrypt('password'),
            'id_role' => $role->id_role,
            'status' => 'aktif',
        ]);

        $kategori = KategoriSurat::create([
            'nama_kategori' => 'Dinas',
            'deskripsi' => 'Surat Dinas',
        ]);

        // Uji query arsip persis seperti yang dilaporkan user
        $response = $this->actingAs($user)->get(route('arsip.index', [
            'tipe' => 'masuk',
            'q' => '',
            'kategori' => $kategori->id_kategori,
            'tgl_mulai' => '',
            'tgl_selesai' => '',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Pusat Arsip Surat Digital Terpadu');
    }
}
