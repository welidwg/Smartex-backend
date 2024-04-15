<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdchaineToOuvriers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->foreignId("id_chaine")->nullable()->references("id")->on("chaines")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            //
        });
    }
}
