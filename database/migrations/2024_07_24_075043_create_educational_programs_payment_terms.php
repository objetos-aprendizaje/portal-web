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
        Schema::create('educational_programs_payment_terms', function (Blueprint $table) {
            $table->uuid("uid")->primary();
            $table->uuid('educational_program_uid');
            $table->foreign('educational_program_uid', 'fk_educational_program_uid')
                ->references('uid')
                ->on('educational_programs')
                ->cascadeOnDelete();
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('finish_date');
            $table->decimal('cost', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_programs_payment_terms');
    }
};
