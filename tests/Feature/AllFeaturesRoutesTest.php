<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllFeaturesRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_menus_and_features_return_ok_for_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::first();
        if (! $admin) {
            $admin = User::first();
        }

        $this->assertNotNull($admin, 'Harus ada user di database');

        $routes = [
            '/',
            '/surat-masuk',
            '/surat-masuk/create',
            '/surat-keluar',
            '/surat-keluar/create',
            '/disposisi',
            '/disposisi/riwayat',
            '/persetujuan',
            '/arsip',
            '/laporan',
            '/master/unit',
            '/master/kategori',
            '/master/jenis',
            '/master/format-nomor',
            '/pengguna',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertStatus(200);
        }

        // Test detail surat masuk jika ada
        $sm = SuratMasuk::first();
        if ($sm) {
            $this->actingAs($admin)->get("/surat-masuk/{$sm->id_surat_masuk}")->assertStatus(200);
            $this->actingAs($admin)->get("/surat-masuk/{$sm->id_surat_masuk}/edit")->assertStatus(200);
            $this->actingAs($admin)->get("/disposisi/buat/{$sm->id_surat_masuk}")->assertStatus(200);
        }

        // Test detail surat keluar jika ada
        $sk = SuratKeluar::first();
        if ($sk) {
            $this->actingAs($admin)->get("/surat-keluar/{$sk->id_surat_keluar}")->assertStatus(200);
            $this->actingAs($admin)->get("/surat-keluar/{$sk->id_surat_keluar}/edit")->assertStatus(200);
            $this->actingAs($admin)->get("/surat-keluar/{$sk->id_surat_keluar}/cetak")->assertStatus(200);
        }
    }
}
