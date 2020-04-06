<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('slug')->nullable()->comment('網址代碼')->index();
            $table->unsignedInteger('display_order')->default(0)->comment('排序權重(大到小)');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '文章分類';
        });
        
        Schema::create('article_tags', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name');
            $table->string('slug')->nullable()->comment('網址代碼')->index();
            $table->unsignedInteger('display_order')->default(0)->comment('排序權重(大到小)');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '文章標籤';
        });
        
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('category_id')->comment('文章分類編號');
            $table->string('title')->comment('文章標題');
            $table->mediumText('content')->nullable()->comment('文章內容');
            $table->json('pictures')->nullable()->comment('文章相簿');
            $table->string('slug')->nullable()->comment('網址代碼')->index();
            $table->unsignedInteger('display_order')->default(0)->comment('排序權重(大到小)');
            $table->timestamp('publish_at')->nullable()->comment('發布時間');
            $table->timestamp('online_at')->nullable()->comment('上架時間');
            $table->timestamp('offline_at')->nullable()->comment('下架時間');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '文章';
        });
        
        Schema::create('articles_tags', function (Blueprint $table) {
            $table->unsignedInteger('article_id')->comment('文章編號');
            $table->unsignedSmallInteger('article_tag_id')->comment('文章標籤編號');
            $table->timestamp('created_at')->nullable();
            $table->primary(['article_id', 'article_tag_id']);
            $table->comment = '文章標籤關聯表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_tags');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('article_tags');
    }
}
