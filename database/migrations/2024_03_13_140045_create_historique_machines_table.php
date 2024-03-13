<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historique_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_machine")->references("id")->on("machines")->onDelete("cascade");
            $table->text("historique");
            $table->dateTime("date_heure");
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
        Schema::dropIfExists('historique_machines');
    }
}
