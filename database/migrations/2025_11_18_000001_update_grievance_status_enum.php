<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para MySQL, precisamos alterar a coluna usando raw SQL
        DB::statement("ALTER TABLE grievances MODIFY COLUMN status ENUM(
            'submitted',
            'under_review',
            'assigned',
            'in_progress',
            'pending_approval',
            'resolved',
            'rejected'
        ) DEFAULT 'submitted'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os estados antigos
        DB::statement("ALTER TABLE grievances MODIFY COLUMN status ENUM(
            'submitted',
            'under_review',
            'in_progress',
            'resolved',
            'closed',
            'rejected'
        ) DEFAULT 'submitted'");
    }
};
