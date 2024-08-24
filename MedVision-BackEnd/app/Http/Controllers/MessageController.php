<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
   
   public function store(Request $request)
   {
       $request->validate([
           'sender_id' => 'required|exists:users,id',
           'receiver_id' => 'required|exists:users,id',
           'message_text' => 'required|string',
           'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // Max 10MB file
       ]);

       $attachmentPath = null;

       if ($request->hasFile('attachment')) {
           $attachmentPath = $request->file('attachment')->store('attachments', 'public');
       }

       $message = Message::create([
           'sender_id' => $request->input('sender_id'),
           'receiver_id' => $request->input('receiver_id'),
           'message_text' => $request->input('message_text'),
           'attachment' => $attachmentPath,
       ]);

       return response()->json($message, 201);
   }

   
   public function index(Request $request)
   {
       $request->validate([
           'sender_id' => 'required|exists:users,id',
           'receiver_id' => 'required|exists:users,id',
       ]);

       $messages = Message::where(function ($query) use ($request) {
           $query->where('sender_id', $request->sender_id)
                 ->where('receiver_id', $request->receiver_id);
       })->orWhere(function ($query) use ($request) {
           $query->where('sender_id', $request->receiver_id)
                 ->where('receiver_id', $request->sender_id);
       })->orderBy('created_at', 'asc')->get();

       return response()->json($messages);
   }

   
   public function show($id)
   {
       $message = Message::findOrFail($id);
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
       $message->update(['read_at' => now()]);

       return response()->json($message);
   }
}
