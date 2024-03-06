<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->foreignId("id_etat")->references("id")->on("etat_machines")->onDelete("cascade");
            $table->foreignId("id_chaine")->references("id")->on("chaines")->onDelete("cascade");
            $table->foreignId("id_reference")->references("id")->on("references")->onDelete("cascade");
            $table->integer("parc");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machines');
    }
}
