<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_number')->unique();
            $table->text('description');
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->text('location_details')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'in_progress', 'resolved', 'closed', 'rejected'])->default('submitted');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_anonymous')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            $table->index(['status', 'priority']);
            $table->index(['user_id', 'status']);
            $table->index('reference_number');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};
