<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->unique();
            $table->text('excerpt')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_url')->nullable();
            $table->string('date')->nullable();
            $table->date('published_at')->nullable();
            $table->json('categories')->nullable(); // Store categories as JSON
            $table->text('full_content')->nullable();
            // $table->timestamps();
            
            
            // $table->index('published_at');
            // $table->index('created_at');


            $table->boolean('is_optimized')->default(false);
            $table->json('reference_articles')->nullable();
            $table->date('optimized_at')->nullable();

            $table->timestamps();

            $table->index('published_at');
            $table->index('created_at');
            $table->index('is_optimized');
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
