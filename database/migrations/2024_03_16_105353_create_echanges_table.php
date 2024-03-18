<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('echanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_machine")->references("id")->on("machines")->onDelete("cascade");
            $table->foreignId("id_chaine_from")->references("id")->on("chaines")->onDelete("cascade");
            $table->foreignId("id_chaine_to")->references("id")->on("chaines")->onDelete("cascade");
            $table->dateTime("date_heure");
            $table->boolean("isActive")->nullable();
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
        Schema::dropIfExists('echanges');
    }
}
