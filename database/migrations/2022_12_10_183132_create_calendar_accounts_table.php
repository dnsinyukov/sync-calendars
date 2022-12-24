<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('provider_id', 100);
            $table->string('provider_type', 100);
            $table->string('name');
            $table->string('email')->index();
            $table->string('picture');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('token')->nullable();
            $table->text('sync_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_accounts');
    }
};
