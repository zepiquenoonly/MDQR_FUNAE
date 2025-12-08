<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // daily, weekly, monthly, quarterly, annual, custom
            $table->date('start_date');
            $table->date('end_date');
            $table->string('format'); // pdf, excel, html
            $table->json('parameters')->nullable();
            $table->string('status')->default('pending'); // pending, generating, completed, failed
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            $table->index(['generated_by', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};