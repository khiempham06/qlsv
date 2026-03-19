<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create("assignments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("teacher_id")->constrained("users")->onDelete("cascade");
            $table->string("title");
            $table->string("file_path");
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists("assignments"); }
}