<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'channel_id' => 'required|exists:channels,id',
        ]);

        $message = Message::create([
            'content' => $request->input('content'),
            'user_id' => auth()->id(),
            'channel_id' => $request->channel_id,
        ]);

        broadcast(new NewMessage($message))->toOthers();
        return response()->json($message->load('user'));
    }

    public function index($channelId)
    {
        $messages = Message::where('channel_id', $channelId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
