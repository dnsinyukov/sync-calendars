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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->string('summary')->nullable();
            $table->string('timezone')->nullable();
            $table->string('provider_id');
            $table->string('provider_type');
            $table->text('description')->nullable();
            $table->text('page_token')->nullable();
            $table->text('sync_token')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('selected')->default(false);
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')->references('id')->on('calendar_accounts')->onDelete('CASCADE');

            $table->index(['provider_id', 'provider_type']);

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
        Schema::dropIfExists('calendars');
    }
};
