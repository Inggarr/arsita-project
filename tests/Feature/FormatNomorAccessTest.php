<?php

namespace Tests\Feature;

use App\Models\FormatNomor;
use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormatNomorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithRole(string $roleName): User
    {
        $role = new Role(['nama_role' => $roleName]);
        $role->id_role = 999;

        $user = new User([
            'id_user' => 999,
            'nama' => 'User Test',
            'username' => 'user_test_'.$roleName,
        ]);
        $user->setRelation('role', $role);

        return $user;
    }

    public function test_non_staff_cannot_access_format_nomor_page(): void
    {
        $pimpinan = $this->makeUserWithRole('pimpinan');
        $response = $this->actingAs($pimpinan)->get(route('master.format-nomor'));
        $response->assertStatus(403);

        $adminRektorat = $this->makeUserWithRole('admin_rektorat');
        $response2 = $this->actingAs($adminRektorat)->get(route('master.format-nomor'));
        $response2->assertStatus(403);
    }

    public function test_staff_tu_can_access_format_nomor_page(): void
    {
        $staff = $this->makeUserWithRole('staff_tu');
        $response = $this->actingAs($staff)->get(route('master.format-nomor'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Penomoran Surat');
        $response->assertSee('Daftar Format Penomoran Otomatis');
    }

    public function test_staff_tu_can_create_update_and_delete_format_nomor(): void
    {
        $unit = Unit::create(['kode_unit' => 'TEST', 'nama_unit' => 'Unit Testing', 'level' => 'unit']);
        $jenis = JenisSurat::create(['kode_jenis' => 'TS', 'nama_jenis' => 'Surat Testing']);
        $staff = $this->makeUserWithRole('staff_tu');

        // 1. Create (Store)
        $storeResponse = $this->actingAs($staff)->post(route('master.format-nomor.store'), [
            'id_unit' => $unit->id_unit,
            'id_jenis_surat' => $jenis->id_jenis_surat,
            'format_nomor' => '{nomor}/TS/TEST/{tahun}',
            'contoh' => '001/TS/TEST/2026',
        ]);
        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('format_nomor', [
            'id_unit' => $unit->id_unit,
            'id_jenis_surat' => $jenis->id_jenis_surat,
            'format_nomor' => '{nomor}/TS/TEST/{tahun}',
        ]);

        $created = FormatNomor::where('format_nomor', '{nomor}/TS/TEST/{tahun}')->first();
        $this->assertNotNull($created);

        // 2. Update
        $updateResponse = $this->actingAs($staff)->put(route('master.format-nomor.update', $created->id_format), [
            'id_unit' => $unit->id_unit,
            'id_jenis_surat' => $jenis->id_jenis_surat,
            'format_nomor' => '{nomor}/TS-REV/TEST/{tahun}',
            'contoh' => '001/TS-REV/TEST/2026',
            'aktif' => '1',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('format_nomor', [
            'id_format' => $created->id_format,
            'format_nomor' => '{nomor}/TS-REV/TEST/{tahun}',
        ]);

        // 3. Delete (Destroy)
        $deleteResponse = $this->actingAs($staff)->delete(route('master.format-nomor.destroy', $created->id_format));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('format_nomor', [
            'id_format' => $created->id_format,
        ]);
    }
}
