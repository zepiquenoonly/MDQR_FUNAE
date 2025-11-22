<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('deadlines')) {
            Schema::create('deadlines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->date('data_aprovacao');
                $table->date('data_inicio');
                $table->date('data_inspecao');
                $table->date('data_finalizacao');
                $table->date('data_inauguracao');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('deadlines');
    }
};