<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_domains', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('domain');
            $table->timestamps();

            $table->unique(['topic', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_domains');
    }
};