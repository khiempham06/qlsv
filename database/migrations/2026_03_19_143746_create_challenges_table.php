<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengesTable extends Migration
{
    public function up()
    {
        Schema::create("challenges", function (Blueprint $table) {
            $table->id();
            $table->foreignId("teacher_id")->constrained("users")->onDelete("cascade");
            $table->text("hint");
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists("challenges"); }
}