<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStoreRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_uuid');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('status');
            $table->uuid('evaluated_by')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

//            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('evaluated_by')->references('uuid')->on('users');
            $table->engine = 'InnoDB';
        });

//        DB::statement('ALTER TABLE store_requests DROP FOREIGN KEY store_requests_user_uuid_foreign');
        DB::statement('ALTER TABLE store_requests ADD FULLTEXT (code, type, status)');
        DB::statement('ALTER TABLE store_requests ADD FOREIGN KEY store_requests_user_uuid_foreign (user_uuid) REFERENCES users (uuid)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_requests');
    }
}
