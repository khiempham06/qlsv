<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === "teacher") {
            $assignments = Assignment::with("submissions.student")->where("teacher_id", Auth::id())->get();
        } else {
            $assignments = Assignment::all();
        }
        return view("assignments.index", compact("assignments"));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== "teacher") return back();
        $path = $request->file("file")->store("assignments");
        Assignment::create([
            "teacher_id" => Auth::id(),
            "title" => $request->title,
            "file_path" => $path
        ]);
        return back()->with("success", "Assignment added");
    }

    public function submit(Request $request, $id)
    {
        if (Auth::user()->role !== "student") return back();
        $path = $request->file("file")->store("submissions");
        Submission::create([
            "assignment_id" => $id,
            "student_id" => Auth::id(),
            "file_path" => $path
        ]);
        return back()->with("success", "Assignment submitted");
    }

    public function show($id)
    {
        $assignment = Assignment::findOrFail($id);
        $path = storage_path("app/" . $assignment->file_path);
        if (!file_exists($path)) {
            return back()->with('error', 'File bài tập không tồn tại.');
        }
        return response()->download($path);
    }

    public function downloadSubmission($id)
    {
        if (Auth::user()->role !== "teacher") return back();
        $sub = Submission::findOrFail($id);
        $path = storage_path("app/" . $sub->file_path);
        if (!file_exists($path)) {
            return back()->with('error', 'File bài nộp không tồn tại.');
        }
        return response()->download($path);
    }
}
