<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketCreated;
use App\Notifications\TicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Показать список заявок пользователя
     */
    public function index()
    {
        $tickets = Auth::user()->tickets()
            ->with(['lastMessage', 'messages'])
            ->withCount(['messages', 'unreadMessagesForUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Показать форму создания новой заявки
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Сохранить новую заявку
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip',
        ]);

        DB::beginTransaction();
        try {
            // Создаём заявку
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);

            // Создаём первое сообщение (описание)
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

            DB::commit();
            
            // Отправляем уведомление админам ПОСЛЕ commit
            $admins = User::where('is_admin', true)->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new NewTicketCreated($ticket));
            }

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Your support ticket has been created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create ticket: ' . $e->getMessage()]);
        }
    }

    /**
     * Показать конкретную заявку
     */
    public function show(Ticket $ticket)
    {
        // Проверяем права доступа
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        // Загружаем сообщения с вложениями
        $ticket->load(['messages.user', 'messages.attachments']);

        // Помечаем непрочитанные сообщения от админа как прочитанные
        $ticket->unreadMessagesForUser()->update(['is_read' => true]);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Добавить ответ к заявке
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Проверяем права доступа
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Проверяем, не закрыта ли заявка
        if ($ticket->isClosed()) {
            return back()->with('error', 'Cannot reply to a closed ticket.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:3',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        // Создаём сообщение
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

        // Обновляем время последнего ответа
        $ticket->update(['last_reply_at' => now()]);

        // Отправляем уведомление назначенному админу или всем админам
        if ($ticket->assigned_to) {
            $ticket->assignedTo->notify(new TicketReplied($ticket, $message));
        } else {
            $admins = User::where('is_admin', true)->get();
            
            if ($admins->count() > 0) {
                Notification::send($admins, new TicketReplied($ticket, $message));
            }
        }

        return back()->with('success', 'Your reply has been added successfully!');
    }

    /**
     * Закрыть заявку
     */
    public function close(Ticket $ticket)
    {
        // Проверяем права доступа
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->update(['status' => Ticket::STATUS_CLOSED]);

        return back()->with('success', 'Ticket has been closed.');
    }

    /**
     * Переоткрыть заявку
     */
    public function reopen(Ticket $ticket)
    {
        // Проверяем права доступа
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }

        $ticket->update(['status' => Ticket::STATUS_OPEN]);

        return back()->with('success', 'Ticket has been reopened.');
    }

    public function checkNewMessages(Ticket $ticket, Request $request)
    {
        $afterId = (int) $request->query('after', 0);
        
        $messages = $ticket->messages()
            ->where('id', '>', $afterId)
            ->with(['user:id,name,avatar', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user->name,
                    'user_avatar' => $msg->user->avatar ? asset('storage/' . $msg->user->avatar) : null,
                    'message' => nl2br(e($msg->message)),
                    'is_admin_reply' => (bool) $msg->is_admin_reply,
                    'created_at' => $msg->created_at->toISOString(),
                    'attachments' => $msg->attachments->map(function($att) {
                        return [
                            'file_name' => $att->file_name,
                            'url' => asset('storage/' . $att->file_path),
                            'file_size' => $att->human_readable_size ?? round($att->file_size / 1024, 2) . ' KB',
                        ];
                    }),
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}
