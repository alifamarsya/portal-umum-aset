<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Response Time SLA (08:00 - 17:00 kerja, max 2 jam)
            $table->timestamp('response_due_at')->nullable()->after('sla_due_at');
            $table->timestamp('responded_at')->nullable()->after('response_due_at');
            $table->string('response_sla_status', 50)->default('pending')->after('responded_at');

            // Resolution Time SLA factors & tracking
            $table->string('skala', 50)->default('Kecil')->after('response_sla_status');
            $table->decimal('estimasi_biaya', 15, 2)->default(0)->after('skala');
            $table->timestamp('resolution_started_at')->nullable()->after('estimasi_biaya');
            $table->unsignedInteger('resolution_hours')->nullable()->after('resolution_started_at');
            $table->timestamp('resolution_due_at')->nullable()->after('resolution_hours');
            $table->timestamp('resolved_at')->nullable()->after('resolution_due_at');
            $table->string('resolution_sla_status', 50)->default('pending')->after('resolved_at');

            // Overall SLA state: on_track, warning, overdue, completed
            $table->string('sla_status', 50)->default('on_track')->after('resolution_sla_status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'response_due_at',
                'responded_at',
                'response_sla_status',
                'skala',
                'estimasi_biaya',
                'resolution_started_at',
                'resolution_hours',
                'resolution_due_at',
                'resolved_at',
                'resolution_sla_status',
                'sla_status',
            ]);
        });
    }
};