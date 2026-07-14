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
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url');   //الـ Endpoint المطلوب
            $table->string('method',10);  //POST GET PUT
            $table->integer('status_code');
            $table->decimal('duration', 8, 4);  // الوقت المستغرق بالثواني (مثال: 0.1245 ثانية)
            $table->string('ip_address',45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();// سيفيد جداً في معرفة البيانات الممررة التي تسببت في بطء الـ Post/Put

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
