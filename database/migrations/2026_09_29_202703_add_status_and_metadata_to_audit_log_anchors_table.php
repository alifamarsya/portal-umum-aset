<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_log_anchors', function (Blueprint $table) {
            $table->string('status', 20)->default('success')->after('block_number');
            $table->unsignedInteger('total_logs')->default(0)->after('status');
            $table->text('error_message')->nullable()->after('total_logs');
            $table->timestamp('anchored_at')->nullable()->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_log_anchors', function (Blueprint $table) {
            $table->dropColumn(['status', 'total_logs', 'error_message', 'anchored_at']);
        });
    }
};
