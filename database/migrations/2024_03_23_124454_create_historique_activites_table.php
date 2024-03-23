<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historique_activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_machine")->nullable()->references("id")->on("machines")->onDelete("cascade");
            $table->foreignId("id_user")->nullable()->references("id")->on("utilisateurs")->onDelete("cascade");
            $table->string("activite");
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
        Schema::dropIfExists('historique_activites');
    }
}
