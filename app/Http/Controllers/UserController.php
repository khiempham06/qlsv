<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view("users.index", compact("users"));
    }

    public function create()
    {
        if (Auth::user()->role !== "teacher") return redirect()->back()->with("error", "Unauthorized");
        return view("users.create");
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== "teacher") return redirect()->back()->with("error", "Unauthorized");
        $data = $request->all();
        $data["password"] = Hash::make($data["password"]);
        User::create($data);
        return redirect()->route("users.index")->with("success", "Tạo user thành công");
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $myId = Auth::id();

        $messages = Message::with('sender')
            ->where(function ($q) use ($id, $myId) {
                $q->where('sender_id', $myId)->where('receiver_id', $id);
            })
            ->orWhere(function ($q) use ($id, $myId) {
                $q->where('sender_id', $id)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view("users.show", compact("user", "messages"));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        if (Auth::user()->role !== "teacher" && Auth::id() != $id) return redirect()->back()->with("error", "Unauthorized");
        return view("users.edit", compact("user"));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (Auth::user()->role !== "teacher" && Auth::id() != $id) return redirect()->back()->with("error", "Unauthorized");

        $data = $request->except(["username", "name"]);
        if (Auth::user()->role === "teacher") {
            $data = $request->all();
        }

        if (!empty($data["password"])) {
            $data["password"] = Hash::make($data["password"]);
        } else {
            unset($data["password"]);
        }

        $user->update($data);
        return redirect()->route("users.index")->with("success", "Cập nhật user thành công");
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== "teacher") return redirect()->back()->with("error", "Unauthorized");
        User::destroy($id);
        return redirect()->route("users.index")->with("success", "Xoá user thành công");
    }
}
