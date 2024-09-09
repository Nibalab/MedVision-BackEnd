<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'sender_type' => 'required|string|in:user,doctor',
            'sender_id' => 'required|integer',
            'receiver_type' => 'required|string|in:user,doctor',
            'receiver_id' => 'required|integer',
            'message_text' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // Max 10MB file
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = Message::create([
            'sender_type' => $request->input('sender_type') === 'user' ? \App\Models\User::class : \App\Models\Doctor::class,
            'sender_id' => $request->input('sender_id'),
            'receiver_type' => $request->input('receiver_type') === 'user' ? \App\Models\User::class : \App\Models\Doctor::class,
            'receiver_id' => $request->input('receiver_id'),
            'message_text' => $request->input('message_text'),
            'attachment' => $attachmentPath,
            'is_read' => false,
        ]);

        return response()->json($message, 201);
    }

    public function index(Request $request)
{
    $authUser = auth()->user();

    // Fetch messages where the doctor is the receiver
    $messages = Message::where('receiver_id', $authUser->id)
        ->where('receiver_type', \App\Models\Doctor::class)
        ->with('sender')  // Load sender relationship to get user names
        ->orderBy('created_at', 'desc') // Order by latest message
        ->get();

    // Prepare response
    $messagesWithSenderInfo = $messages->map(function ($message) {
        return [
            'id' => $message->id,
            'message_text' => $message->message_text,
            'is_read' => $message->is_read,
            'sender_id' => $message->sender_id,  // Include sender_id here
            'sender_type' => get_class($message->sender),  // Add sender_type (User or Doctor)
            'sender_name' => $message->sender->name ?? 'Unknown Sender',
            'sender_profile_picture' => $message->sender->profile_picture ?? null,
            'created_at' => $message->created_at,
        ];
    });

    // Count unread messages
    $unreadCount = Message::where('receiver_id', $authUser->id)
        ->where('receiver_type', \App\Models\Doctor::class)
        ->where('is_read', false)
        ->count();

    return response()->json([
        'messages' => $messagesWithSenderInfo,
        'unread_count' => $unreadCount,
    ]);
}

    

    
    

    public function show($id)
    {
        $message = Message::findOrFail($id);
        if ($message->attachment) {
            $message->attachment_url = asset('storage/' . $message->attachment);
        }
        return response()->json($message);
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);

        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        $message->update([
            'read_at' => now(),
            'is_read' => true
        ]);

        return response()->json($message);
    }
}
