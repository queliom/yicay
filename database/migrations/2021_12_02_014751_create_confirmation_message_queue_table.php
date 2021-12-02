<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateConfirmationMessageQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('confirmation_message_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mtype');
            $table->text('body');
            $table->dateTime('created_at')->default(DB::raw('NOW()'));;
            $table->string('situation', 25)->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('confirmation_message_queue');
    }
}
