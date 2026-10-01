<?php

namespace Tests\Feature;

use App\Jobs\WeeklyBlockchainAnchorJob;
use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\BlockchainAnchorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BlockchainAnchorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'username'  => 'admin_test',
            'role_id'   => 1,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_audit_log_page_with_blockchain_section(): void
    {
        // Buat dummy audit log
        AuditLog::create([
            'username'   => 'admin_test',
            'aksi'       => 'CREATE',
            'modul'      => 'Sistem Tiket',
            'entitas'    => 'Ticket',
            'entitas_id' => 1,
            'keterangan' => 'Membuat tiket uji coba',
        ]);

        // Buat dummy anchor
        AuditLogAnchor::create([
            'root_hash'     => '0x' . str_repeat('a', 64),
            'tx_hash'       => '0x' . str_repeat('b', 64),
            'block_number'  => 1234567,
            'periode_awal'  => now()->subWeek()->startOfWeek(),
            'periode_akhir' => now()->subWeek()->endOfWeek(),
            'status'        => AuditLogAnchor::STATUS_SUCCESS,
            'total_logs'    => 1,
            'anchored_at'   => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-log.index'));
        $response->assertStatus(200);
        $response->assertSee('Audit Log &amp; Verifikasi Integritas Blockchain', false);
        $response->assertSee('Bukti Publik Blockchain (Sepolia Testnet)');
        $response->assertSee('Jalankan Anchoring Sekarang');
        $response->assertSee('Sukses (Sepolia)');
        $response->assertSee('Block #1234567');
        $response->assertSee('Etherscan Explorer');
    }

    public function test_weekly_blockchain_anchor_job_can_be_dispatched(): void
    {
        Queue::fake();

        WeeklyBlockchainAnchorJob::dispatch();

        Queue::assertPushed(WeeklyBlockchainAnchorJob::class);
    }

    public function test_admin_can_trigger_anchor_endpoint(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.audit-log.trigger-anchor'), [
            'async' => true,
        ]);

        $response->assertRedirect();
        Queue::assertPushed(WeeklyBlockchainAnchorJob::class);
    }

    public function test_service_generates_correct_root_hash(): void
    {
        // Buat dua log
        $log1 = AuditLog::create([
            'username'   => 'admin_test',
            'aksi'       => 'CREATE',
            'modul'      => 'Tiket',
            'entitas'    => 'Ticket',
            'entitas_id' => 1,
            'keterangan' => 'Log 1',
        ]);

        $log2 = AuditLog::create([
            'username'   => 'admin_test',
            'aksi'       => 'UPDATE',
            'modul'      => 'Tiket',
            'entitas'    => 'Ticket',
            'entitas_id' => 1,
            'keterangan' => 'Log 2',
        ]);

        $expectedRootHash = '0x' . hash('sha256', $log1->hash . $log2->hash);

        $service = app(BlockchainAnchorService::class);
        $hashes = AuditLog::whereBetween('created_at', [
            now()->startOfWeek()->startOfDay(),
            now()->endOfWeek()->endOfDay()
        ])->orderBy('id')->pluck('hash');

        $computedRootHash = '0x' . hash('sha256', $hashes->implode(''));

        $this->assertEquals($expectedRootHash, $computedRootHash);
    }
}
