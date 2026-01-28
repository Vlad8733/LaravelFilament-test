<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        $tickets = $u->tickets()
            ->withCount('messages')
            ->withCount(['messages as unread_messages_for_user_count' => fn ($q) => $q->where('is_admin_reply', true)->where('is_read', false)])
            ->with(['messages', 'latestMessage'])->orderBy('created_at', 'desc')->paginate(10);

        return view('tickets.index', [
            'tickets' => $tickets,
            'stats' => [
                'total' => $u->tickets()->count(), 'open' => $u->tickets()->where('status', 'open')->count(),
                'in_progress' => $u->tickets()->where('status', 'in_progress')->count(), 'closed' => $u->tickets()->where('status', 'closed')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $r)
    {
        $v = $r->validate([
            'subject' => 'required|string|max:255', 'priority' => 'required|string|in:low,medium,high,urgent',
            'description' => 'required|string|min:10', 'attachments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $t = Ticket::create(['user_id' => Auth::id(), 'subject' => $v['subject'], 'description' => $v['description'], 'priority' => $v['priority'], 'status' => 'open']);
            $msg = $t->messages()->create(['user_id' => Auth::id(), 'message' => $v['description'], 'is_admin_reply' => false]);
            $this->saveAttachments($r, $msg);

            $admins = User::where('role', User::ROLE_ADMIN)->get();
            if ($admins->count()) {
                Notification::send($admins, new NewTicketCreated($t));
            }
            DB::commit();

            return redirect()->route('tickets.show', $t)->with('success', 'Ticket created!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }
        $ticket->messages()->where('is_admin_reply', true)->where('is_read', false)->update(['is_read' => true]);
        $ticket->load(['messages.user', 'messages.attachments']);

        return view('tickets.show', ['ticket' => $ticket]);
    }

    public function reply(Request $r, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }
        $v = $r->validate(['message' => 'required|string|min:1', 'attachments.*' => 'nullable|file|max:10240']);

        DB::beginTransaction();
        try {
            $msg = $ticket->messages()->create(['user_id' => Auth::id(), 'message' => $v['message'], 'is_admin_reply' => false]);
            $this->saveAttachments($r, $msg);
            if ($ticket->status === 'closed') {
                $ticket->update(['status' => 'open']);
            }
            $ticket->update(['last_reply_at' => now()]);
            DB::commit();

            if ($r->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg->load(['user', 'attachments'])]);
            }

            return back()->with('success', 'Reply sent!');
        } catch (\Exception $e) {
            DB::rollBack();

            return $r->wantsJson() ? response()->json(['success' => false, 'error' => $e->getMessage()], 500) : back()->with('error', 'Failed');
        }
    }

    public function close(Ticket $t)
    {
        if ($t->user_id !== Auth::id()) {
            abort(403);
        }
        $t->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed');
    }

    public function reopen(Ticket $t)
    {
        if ($t->user_id !== Auth::id()) {
            abort(403);
        }
        $t->update(['status' => 'open']);

        return back()->with('success', 'Ticket reopened');
    }

    public function checkNewMessages(Request $r, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }
        $after = $r->get('after', 0);

        $msgs = $ticket->messages()->where('id', '>', $after)->with(['user', 'attachments'])->orderBy('id')->get()->map(fn ($m) => [
            'id' => $m->id, 'message' => $m->message, 'is_admin_reply' => $m->is_admin_reply,
            'user_name' => $m->user->name, 'created_at' => $m->created_at->diffForHumans(),
            'user_avatar' => $m->user->avatar ? asset('storage/'.$m->user->avatar) : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($m->user->email))).'?s=80&d=identicon',
            'attachments' => $m->attachments->map(fn ($a) => ['file_name' => $a->file_name, 'file_size' => $a->human_readable_size ?? '', 'url' => asset('storage/'.$a->file_path), 'is_image' => str_starts_with($a->file_type ?? '', 'image/')]),
        ]);

        $ticket->messages()->where('id', '>', $after)->where('is_admin_reply', true)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['messages' => $msgs]);
    }

    private function saveAttachments(Request $r, $msg): void
    {
        if (! $r->hasFile('attachments')) {
            return;
        }
        foreach ($r->file('attachments') as $f) {
            $msg->attachments()->create([
                'file_name' => $f->getClientOriginalName(), 'file_path' => $f->store('ticket-attachments', 'public'),
                'file_type' => $f->getMimeType(), 'file_size' => $f->getSize(),
            ]);
        }
    }
}
