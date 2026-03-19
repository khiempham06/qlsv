<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $msg = Message::create([
            "sender_id" => Auth::id(),
            "receiver_id" => $request->receiver_id,
            "content" => $request->content
        ]);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(["success" => true, "message" => $msg]);
        }
        return back()->with("success", "Gửi tin nhắn thành công");
    }

    public function fetchMessages($id)
    {
        $myId = Auth::id();
        $messages = Message::with('sender')
            ->where(function ($q) use ($id, $myId) {
                $q->where('sender_id', $myId)->where('receiver_id', $id);
            })
            ->orWhere(function ($q) use ($id, $myId) {
                $q->where('sender_id', $id)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')->get();
        return response()->json($messages);
    }

    public function update(Request $request, $id)
    {
        $msg = Message::findOrFail($id);
        if ($msg->sender_id == Auth::id()) {
            $msg->update(["content" => $request->content]);
        }
        return back()->with("success", "Tin nhắn đã được cập nhật");
    }

    public function destroy($id)
    {
        $msg = Message::findOrFail($id);
        if ($msg->sender_id == Auth::id() || Auth::user()->role === "teacher") {
            $msg->delete();
        }
        return back()->with("success", "Tin nhắn đã được xóa");
    }
}
