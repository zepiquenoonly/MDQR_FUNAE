<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('objectives')) {
            Schema::create('objectives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id');
                $table->string('title');
                $table->text('description');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }   

    public function down()
    {
        Schema::dropIfExists('objectives');
    }
};