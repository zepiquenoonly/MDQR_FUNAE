<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('finances')) {
            Schema::create('finances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->string('financiador');
                $table->string('beneficiario');
                $table->string('responsavel');
                $table->string('valor_financiado');
                $table->string('codigo');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('finances');
    }
};