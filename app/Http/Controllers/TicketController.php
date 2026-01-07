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
    /**
     * Список тикетов пользователя
     */
    public function index()
    {
        $tickets = Auth::user()->tickets()
            ->withCount('messages')
            ->withCount(['messages as unread_messages_for_user_count' => function ($query) {
                $query->where('is_admin_reply', true)->where('is_read', false);
            }])
            ->with(['messages', 'latestMessage'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Auth::user()->tickets()->count(),
            'open' => Auth::user()->tickets()->where('status', 'open')->count(),
            'in_progress' => Auth::user()->tickets()->where('status', 'in_progress')->count(),
            'closed' => Auth::user()->tickets()->where('status', 'closed')->count(),
        ];

        return view('tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Форма создания нового тикета
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Сохранение нового тикета
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'description' => 'required|string|min:10',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        DB::beginTransaction();
        try {
            // Создаём тикет
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);

            // Создаём первое сообщение
            $message = $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validated['description'],
                'is_admin_reply' => false,
            ]);

            // Обрабатываем вложения
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments', 'public');
                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Уведомляем админов о новом тикете
            $admins = User::where('role', User::ROLE_ADMIN)->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new NewTicketCreated($ticket));
            }

            DB::commit();

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Ticket created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to create ticket: '.$e->getMessage());
        }
    }

    /**
     * Показать тикет (чат)
     */
    public function show(Ticket $ticket)
    {
        // Проверяем что тикет принадлежит пользователю
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Помечаем сообщения от админа как прочитанные
        $ticket->messages()
            ->where('is_admin_reply', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $ticket->load(['messages.user', 'messages.attachments']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Ответ на тикет
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Проверяем что тикет принадлежит пользователю
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $message = $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'is_admin_reply' => false,
            ]);

            // Обрабатываем вложения
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments', 'public');
                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Обновляем статус тикета если он был закрыт
            if ($ticket->status === 'closed') {
                $ticket->update(['status' => 'open']);
            }

            $ticket->update(['last_reply_at' => now()]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message->load(['user', 'attachments']),
                ]);
            }

            return back()->with('success', 'Reply sent!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to send reply');
        }
    }

    /**
     * Закрыть тикет
     */
    public function close(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed');
    }

    public function reopen(Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->update(['status' => 'open']);

        return back()->with('success', 'Ticket reopened');
    }
    
    /**
     * Check for new messages (for real-time polling)
     */
    public function checkNewMessages(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }
        
        $afterId = $request->get('after', 0);
        
        $messages = $ticket->messages()
            ->where('id', '>', $afterId)
            ->with(['user', 'attachments'])
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_admin_reply' => $message->is_admin_reply,
                    'user_name' => $message->user->name,
                    'user_avatar' => $message->user->avatar 
                        ? asset('storage/' . $message->user->avatar)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($message->user->email))) . '?s=80&d=identicon',
                    'created_at' => $message->created_at->diffForHumans(),
                    'attachments' => $message->attachments->map(function ($att) {
                        $isImage = str_starts_with($att->file_type ?? '', 'image/');
                        return [
                            'file_name' => $att->file_name,
                            'file_size' => $att->human_readable_size ?? '',
                            'url' => asset('storage/' . $att->file_path),
                            'is_image' => $isImage,
                        ];
                    }),
                ];
            });
            
        // Mark admin messages as read
        $ticket->messages()
            ->where('id', '>', $afterId)
            ->where('is_admin_reply', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json(['messages' => $messages]);
    }
}
