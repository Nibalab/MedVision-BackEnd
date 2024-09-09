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
        $senderId = $request->input('sender_id', null);  // Optional parameter
    
        $messagesQuery = Message::where('receiver_id', $authUser->id)
            ->where('receiver_type', \App\Models\Doctor::class);
    
        if ($senderId) {
            $messagesQuery->where('sender_id', $senderId);
        }
    
        $messages = $messagesQuery->with('sender')  // Load sender relationship to get user names
            ->orderBy('created_at', 'desc') // Order by latest message
            ->get();
    
        $messagesWithSenderInfo = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message_text' => $message->message_text,
                'is_read' => $message->is_read,
                'sender_id' => $message->sender_id,
                'sender_type' => get_class($message->sender),
                'sender_name' => $message->sender->name ?? 'Unknown Sender',
                'sender_profile_picture' => $message->sender->profile_picture ?? null,
                'created_at' => $message->created_at,
            ];
        });
    
        return response()->json([
            'messages' => $messagesWithSenderInfo,
            'unread_count' => $messages->where('is_read', false)->count(),
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
        // Fetch the authenticated user
        $authUser = auth()->user();
    
        // Find the message by ID
        $message = Message::findOrFail($id);
    
        // Check if the authenticated user is the sender of the message
        if ($message->sender_id !== $authUser->id) {
            return response()->json(['error' => 'You are not authorized to mark this message as read'], 403);
        }
    
        // Update the message as read
        $message->update([
            'read_at' => now(),
            'is_read' => true
        ]);
    
        return response()->json(['message' => 'Message marked as read successfully']);
    }
    
}
