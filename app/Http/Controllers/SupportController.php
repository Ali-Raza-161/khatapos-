<?php
namespace App\Http\Controllers;
use App\Models\SupportTicket; use App\Models\ActivityLog;
use Illuminate\Http\Request;
class SupportController extends Controller {
    public function index() {
        return view('support.index', ['tickets' => SupportTicket::orderByDesc('created_at')->get()]);
    }
    public function store(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'subject' => 'required|string', 'message' => 'required|string', 'priority' => 'required|in:low,normal,high']);
        $data['user_id'] = auth()->id();
        $t = SupportTicket::create($data);
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'ticket_created', 'detail' => "Support ticket #{$t->id}: {$t->subject}"]);
        return back()->with('ok', 'Support ticket created. We will follow up on it.');
    }
    public function update(Request $request, SupportTicket $ticket) {
        $data = $request->validate(['status' => 'required|in:open,in_progress,resolved', 'reply_note' => 'nullable|string']);
        $ticket->update($data);
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'ticket_updated', 'detail' => "Support ticket #{$ticket->id} set to {$data['status']}"]);
        return back()->with('ok', 'Ticket updated.');
    }
}
