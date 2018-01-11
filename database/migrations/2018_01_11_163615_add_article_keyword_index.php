<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArticleKeywordIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_keywords', function (Blueprint $table) {
            $table->index('article_id', 'article_id_index');
            $table->index('keyword_id', 'keyword_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('article_keywords', function (Blueprint $table) {
            $table->dropIndex('article_id_index');
            $table->dropIndex('keyword_id_index');
        });
    }
}
