<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('department_indicators')->onDelete('cascade');
            $table->date('record_date');
            $table->decimal('value', 12, 4);
            $table->json('breakdown')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['indicator_id', 'record_date']);
            $table->index('record_date');
            $table->index('indicator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_records');
    }
};