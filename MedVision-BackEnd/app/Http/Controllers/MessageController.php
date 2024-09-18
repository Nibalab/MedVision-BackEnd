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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', 
        ]);
        
        \Log::info('Sender ID:', ['sender_id' => $request->input('sender_id')]);

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
        \Log::info('Message created:', ['message' => $message]);

        return response()->json($message, 201);
    }

    public function index(Request $request)
{
    $authUser = auth()->user();  // Get the authenticated user (can be patient or doctor)
    $senderId = $request->input('sender_id', null);  // Optional sender_id parameter

    // Query for both received and sent messages
    $messagesQuery = Message::where(function ($query) use ($authUser) {
        // Received messages
        $query->where('receiver_id', $authUser->id); // The receiver is the authenticated user (doctor's user_id or patient's id)
    })
    ->orWhere(function ($query) use ($authUser) {
        // Sent messages
        $query->where('sender_id', $authUser->id);  // The sender is the authenticated user (doctor's user_id or patient's id)
    });

    // If sender_id is provided, filter the messages by that sender
    if ($senderId) {
        $messagesQuery->where(function ($q) use ($senderId) {
            $q->where('sender_id', $senderId)
              ->orWhere('receiver_id', $senderId);
        });
    }

    $messages = $messagesQuery->with('sender')  // Load sender relationship to get user names
        ->orderBy('created_at', 'desc')         // Order by latest message
        ->get();

    // Map messages to include sender information
    $messagesWithSenderInfo = $messages->map(function ($message) {
        // Ensure sender is not null before using get_class()
        $senderClass = $message->sender ? get_class($message->sender) : null;

        return [
            'id' => $message->id,
            'message_text' => $message->message_text,
            'is_read' => $message->is_read,
            'sender_id' => $message->sender_id,
            'sender_type' => $senderClass,
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
    $message = Message::findOrFail($id);

    // If the message is not read, update it
    if (!$message->is_read) {
        $message->is_read = true;
        $message->read_at = now();
        $message->save();
    }

    return response()->json(['status' => 'success', 'message' => 'Message marked as read']);
}

public function sendMessageFromPatient(Request $request)
{
    \Log::info('Patient Message Request:', $request->all());

    // Validate the incoming request
    $request->validate([
        'sender_type' => 'required|string|in:user',
        'sender_id' => 'required|integer',
        'receiver_type' => 'required|string|in:doctor',
        'receiver_id' => 'required|integer',
        'message_text' => 'required|string',
    ]);

    // Find the doctor by ID to get the user_id
    $doctor = \App\Models\Doctor::find($request->input('receiver_id'));
    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found.'], 404);
    }

    // Use the doctor's user_id as the receiver_id
    $message = Message::create([
        'sender_type' => \App\Models\User::class,
        'sender_id' => $request->input('sender_id'),  // Patient ID
        'receiver_type' => \App\Models\User::class,   // Receiver is a User (doctor's user_id)
        'receiver_id' => $doctor->user_id,            // Use the user_id of the doctor
        'message_text' => $request->input('message_text'),
        'is_read' => false,
    ]);

    \Log::info('Patient message created:', ['message' => $message]);

    return response()->json($message, 201);
}

public function getPatientConversations()
{
    $authUser = auth()->user(); // Get the authenticated patient

    if ($authUser->role !== 'patient') {
        return response()->json(['error' => 'Unauthorized'], 403); // Ensure only patients can access this route
    }

    // Fetch distinct doctors that the patient has exchanged messages with
    $conversations = Message::where('sender_id', $authUser->id)
        ->orWhere('receiver_id', $authUser->id)
        ->with(['sender', 'receiver'])
        ->get()
        ->groupBy(function ($message) use ($authUser) {
            return $message->sender_id === $authUser->id ? $message->receiver_id : $message->sender_id;
        });

    // Format conversations for the frontend
    $formattedConversations = $conversations->map(function ($messages, $doctorId) use ($authUser) {
        $lastMessage = $messages->last(); // Get the last message exchanged
        $doctor = $lastMessage->sender_id === $authUser->id ? $lastMessage->receiver : $lastMessage->sender;

        return [
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'doctor_profile_picture' => $doctor->profile_picture,
            'last_message' => $lastMessage->message_text,
            'unread_count' => $messages->where('is_read', false)->count(),
        ];
    });

    return response()->json(['chats' => $formattedConversations->values()]);
}

    
}
