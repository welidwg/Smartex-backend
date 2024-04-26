<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOuvrierMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ouvrier_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_ouvrier")->references("id")->on("ouvriers")->onDelete("cascade");
            $table->foreignId("id_reference")->references("id")->on("references")->onDelete("cascade");
            $table->unique(["id_ouvrier", "id_reference"]);
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
        Schema::dropIfExists('ouvrier_machines');
    }
}
