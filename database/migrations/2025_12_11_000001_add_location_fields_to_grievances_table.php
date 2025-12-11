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
        Schema::table('grievances', function (Blueprint $table) {
            $table->string('municipal_district')->nullable()->after('district');
            $table->string('administrative_post')->nullable()->after('municipal_district');
            $table->string('locality')->nullable()->after('administrative_post');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table) {
            $table->dropColumn(['municipal_district', 'administrative_post', 'locality']);
        });
    }
};
