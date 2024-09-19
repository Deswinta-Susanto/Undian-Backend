<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKandidatHadiahUmumsTable extends Migration
{
    public function up()
    {
        Schema::create('kandidat_hadiah_umum', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nipp');
            $table->string('jabatan');
            $table->string('unit');
            $table->enum('status', ['sudah menang', 'belum menang']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kandidat_hadiah_umum');
    }
}
