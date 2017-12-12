<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('article_url', 250);
            $table->integer('article_id')->nullable();
            $table->datetime('last_crawled')->nullable();
            $table->timestamps();
            $table->smallInteger('times_scanned')->default(0);
            $table->boolean('curr_scan')->default(false);
            $table->boolean('extracted_keywords')->default(false);
            $table->smallInteger('num_fail_scans')->default(0);
            $table->string('article_hash', 50);
            $table->string('parsed_by', 50)->nullable();
            $table->integer('recrawl_interval')->nullable();
            $table->boolean('active_crawl')->nullable();
            $table->smallInteger('failed_status_code')->nullable();

            $table->unique('article_hash');
            $table->unique('article_url');
            $table->index('active_crawl');
            $table->index('last_crawled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('urls');
    }
}
