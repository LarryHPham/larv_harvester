<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('elastic_index_id', 40)->nullable();
            $table->timestamps();
            $table->string('article_url', 255);
            $table->string('url_hash', 40);
            $table->string('path_to_file', 127)->nullable();
            $table->boolean('disabled')->default(0);
            $table->string('index_type', 50)->nullable();

            $table->unique('url_hash');
            $table->unique(['elastic_index_id', 'index_type']);
            $table->index('disabled');
            $table->index('path_to_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger');
    }
}
