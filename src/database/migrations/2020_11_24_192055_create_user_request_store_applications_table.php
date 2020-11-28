<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestStoreApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_store_applications', function (Blueprint $table) {
            $table->id();
            $table->string('user_request_code');
            $table->uuid('uuid')->nullable();
            $table->string('name');
            $table->string('contact_number');
            $table->string('address');
            $table->string('map_coordinates');
            $table->string('map_address');
            $table->date('open_until');
            $table->string('attachment');

            $table->foreign('user_request_code')->references('code')->on('user_requests');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_request_store_applications');
    }
}
