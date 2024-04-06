<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdpanneToHistoriqueMachines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historique_machines', function (Blueprint $table) {
            $table->foreignId("id_panne")->nullable()->references("id")->on("panne_machines")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historique_machines', function (Blueprint $table) {
            //
        });
    }
}
