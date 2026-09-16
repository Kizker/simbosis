<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(15);
        return Inertia::render('Admin/Messages/Index', [
            'messages' => $messages
        ]);
    }

    public function reply(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'reply_message' => 'required|string',
        ]);

        try {
            Mail::raw($data['reply_message'], function ($m) use ($message) {
                $m->to($message->email)
                  ->subject('Balasan: [Kontak] Simbiosis News');
            });

            $message->update([
                'is_read' => true,
                'replied_at' => now(),
            ]);

            return back()->with('success', 'Balasan berhasil dikirim.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email balasan kontak: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email balasan. Periksa konfigurasi SMTP.');
        }
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Pesan berhasil dihapus.');
    }

    public function markAsRead(ContactMessage $message)
    {
        $message->update(['is_read' => true]);
        return back()->with('success', 'Pesan ditandai sudah dibaca.');
    }
}
