<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('email_verification_tokens', function (Blueprint $table) {
        $table->id(); // Para la columna de ID auto-incremental
        $table->string('user_id', 36); // Coincide con `uid` en `usuarios`
        $table->string('token');
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('uid')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('email_verification_tokens');
}
};
