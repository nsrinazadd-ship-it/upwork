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
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('bio');
            $table->unsignedInteger('hourly_rate');
            $table->string('phone_number',20)->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('availability',['available', 'busy', 'unavailable'])->default('available');
            $table->boolean('is_verified')->default(false);
            $table->json('portfolio_links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelancer_profiles');
    }
};
