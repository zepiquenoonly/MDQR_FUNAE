<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->string('image_url')->nullable();
                $table->string('provincia');
                $table->string('distrito');
                $table->string('bairro');
                $table->enum('category', ['andamento', 'parados', 'finalizados'])->default('andamento');
                $table->date('data_criacao');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};