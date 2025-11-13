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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('autoload')->default(true);
            $table->boolean('is_translatable')->default(false);
            $table->text('fixed_value')->nullable();
            $table->timestamps();

            $table->index('key');
            $table->index('group');
            $table->index('is_active');
            $table->index('autoload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

