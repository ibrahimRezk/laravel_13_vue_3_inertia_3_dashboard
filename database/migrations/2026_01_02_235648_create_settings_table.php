<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('address');
            $table->boolean('active')->default('1')->comment('واحد مفعل - صفر معطل');
            $table->string('phone', 250);
            $table->string('email', 100);

            $table->foreignId('added_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');

            $table->json('weekendDays')->nullable();
            // $table->json('vacationsDates')->nullable();
            $table->string('logo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
