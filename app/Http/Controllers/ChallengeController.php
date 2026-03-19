<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function index()
    {
        $challenges = Challenge::all();
        return view("challenges.index", compact("challenges"));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== "teacher") return back();
        $challenge = Challenge::create([
            "teacher_id" => Auth::id(),
            "hint" => $request->hint
        ]);

        if ($request->hasFile("file_txt")) {
            $file = $request->file("file_txt");
            $filename = strtolower($file->getClientOriginalName());
            $file->storeAs("challenges/" . $challenge->id, $filename);
        }
        return back()->with("success", "Quizz tạo thành công");
    }

    public function submitAnswer(Request $request, $id)
    {
        $answer = strtolower($request->answer) . ".txt";
        $dir = "challenges/" . $id;
        $files = Storage::files($dir);

        if (count($files) > 0) {
            $actual = basename($files[0]);
            if ($answer === strtolower($actual)) {
                $content = Storage::get($files[0]);
                return back()->with("success", "Chuẩn! " . $content);
            }
        }
        return back()->with("error", "Sai rồi!");
    }
}
