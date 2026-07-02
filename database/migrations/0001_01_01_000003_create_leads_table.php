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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nama_customer', 100);
            $table->string('no_hp', 20);
            $table->string('phone_key', 20)->nullable();
            $table->enum('status_prospek', ['New', 'Cold', 'Warm', 'Hot', 'Deal', 'Tidak Respon', 'Tidak Berminat'])->default('New');
            $table->tinyInteger('fase_followup')->default(0);
            $table->date('tgl_next_followup')->nullable();
            $table->text('catatan_terakhir')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->index('status_prospek');
            $table->index('tgl_next_followup');
            $table->index('phone_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
