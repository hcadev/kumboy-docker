<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid');
            $table->dateTime('date_recorded');
            $table->text('action_taken');

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->engine = 'InnoDB';
        });

        DB::statement('ALTER TABLE user_activities ADD FULLTEXT (action_taken)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_activities');
    }
}
