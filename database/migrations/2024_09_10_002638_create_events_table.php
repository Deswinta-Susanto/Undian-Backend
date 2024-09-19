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
    Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->string('nama_event');
        $table->date('tanggal_mulai');
        $table->date('tanggal_selesai');
        $table->integer('total_pemenang')->nullable(); // atau total_hadiah
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('events');
}

};