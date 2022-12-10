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
        Schema::create('oauth2_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 100);
            $table->string('name');
            $table->string('email')->index();
            $table->string('picture');
            $table->string('provider')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('token')->nullable();
            $table->dateTime('expires_at')->nullable();
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
        Schema::dropIfExists('oauth2_accounts');
    }
};
