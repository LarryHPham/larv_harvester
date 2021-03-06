<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id');
            $table->integer('keyword_id');
            $table->enum('keyword_type', ['App\Keyword', 'App\KeywordModified']);
            $table->smallInteger('weight')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_keywords');
    }
}
