<?php
namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $channels = $user->channels;
        return response()->json($channels);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_private' => 'required|boolean',
            'user_ids' => 'required|array'
        ]);

        $channel = Channel::create([
            'name' => $request->name,
            'is_private' => $request->is_private
        ]);

        $channel->users()->attach($request->user_ids);

        return response()->json($channel, 201);
    }

    public function createPrivateChannel(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUserId = auth()->id();
        $otherUserId = $request->user_id;

        // Kiểm tra xem đã có channel private giữa hai người chưa
        $existingChannel = Channel::whereHas('users', function($query) use ($currentUserId) {
            $query->where('user_id', $currentUserId);
        })->whereHas('users', function($query) use ($otherUserId) {
            $query->where('user_id', $otherUserId);
        })->where('is_private', true)->first();

        if ($existingChannel) {
            return response()->json(['channel' => $existingChannel]);
        }

        // Tạo channel mới nếu chưa tồn tại
        $channel = Channel::create([
            'name' => "Private: $currentUserId-$otherUserId",
            'is_private' => true
        ]);

        $channel->users()->attach([$currentUserId, $otherUserId]);

        return response()->json(['channel' => $channel], 201);
    }

    public function show(Channel $channel)
    {
        $channel->load('users');
        return response()->json($channel);
    }

    public function addUser(Request $request, Channel $channel)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $channel->users()->attach($request->user_id);

        return response()->json(['message' => 'User added to channel']);
    }
}
