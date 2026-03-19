<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create("submissions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("assignment_id")->constrained("assignments")->onDelete("cascade");
            $table->foreignId("student_id")->constrained("users")->onDelete("cascade");
            $table->string("file_path");
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists("submissions"); }
}