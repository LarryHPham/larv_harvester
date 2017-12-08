<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrawlOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawl_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id');
            $table->smallInteger('weight')->default(0); // Default to 0 to allow easy incrementing
            $table->boolean('scheduled')->default(false);
            $table->boolean('get_urls')->default(true);
            $table->boolean('get_content')->default(true);
            $table->datetime('claimed_at')->nullable();
            $table->timestamps();

            $table->unique('article_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawl_order');
    }
}
