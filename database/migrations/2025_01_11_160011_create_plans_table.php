<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, places: 2); // Amount in decimals
            $table->string('billing_interval'); // monthly or yearly
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
