<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->nullable();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('channel_id')->index();
            $table->unsignedInteger('replies_count')->default(0);
            $table->unsignedInteger('best_reply_id')->nullable();
            $table->unsignedInteger('visits')->default(0);
            $table->boolean('locked')->default(false);
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->foreign('best_reply_id')
                ->references('id')
                ->on('replies')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threads');
    }
}
