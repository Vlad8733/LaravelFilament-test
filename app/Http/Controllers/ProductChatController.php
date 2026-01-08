<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductChat;
use App\Models\ProductChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductChatController extends Controller
{
    /**
     * Show or create chat for a product
     */
    public function show(Request $request, Product $product)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', __('Please login to chat with seller'));
        }

        // Get product with all needed relationships
        $product->load(['company.owner', 'seller']);

        // Приоритет определения продавца:
        // 1. user_id (прямой продавец продукта)
        // 2. company->user_id (владелец компании)
        $sellerId = $product->user_id ?? $product->company?->user_id;

        if (! $sellerId) {
            return back()->with('error', __('This product does not have a seller assigned'));
        }

        $seller = User::find($sellerId);

        // Проверка что продавец существует и не является админом
        if (! $seller) {
            return back()->with('error', __('Seller not found'));
        }

        if ($seller->hasRole('super_admin') || $seller->hasRole('admin')) {
            return back()->with('error', __('Cannot create chat with administrator. Please contact the seller of this product.'));
        }

        // Дополнительная проверка: продавец должен иметь роль seller
        if (! $seller->hasRole('seller') && ! $seller->is_seller) {
            return back()->with('error', __('This user is not registered as a seller.'));
        }

        // Find or create chat
        $chat = ProductChat::firstOrCreate(
            [
                'product_id' => $product->id,
                'customer_id' => Auth::id(),
                'seller_id' => $sellerId,
            ],
            [
                'status' => 'open',
            ]
        );

        // Load messages with users
        $chat->load(['messages.user', 'product', 'seller', 'customer']);

        // Mark messages as read
        $isSeller = Auth::id() === $sellerId;
        $chat->markMessagesAsRead($isSeller);

        return view('product-chat.show', compact('chat', 'product'));
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, ProductChat $chat)
    {
        // Check authorization
        if ($chat->customer_id !== Auth::id() && $chat->seller_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        DB::beginTransaction();
        try {
            $isSeller = $chat->seller_id === Auth::id();

            $messageData = [
                'product_chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'is_seller' => $isSeller,
            ];

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('product-chat-attachments', 'public');
                $messageData['attachment_path'] = $path;
                $messageData['attachment_name'] = $file->getClientOriginalName();
                $messageData['attachment_type'] = $file->getMimeType();
            }

            $message = ProductChatMessage::create($messageData);

            // Load user relationship
            $message->load('user');

            // Update chat
            $chat->update([
                'last_message_at' => now(),
                'last_message_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => [
                        'id' => $message->id,
                        'user_id' => $message->user_id,
                        'message' => $message->message,
                        'is_seller' => $message->is_seller,
                        'user_name' => $message->user->name,
                        'user_avatar' => $message->user->avatar
                            ? asset('storage/'.$message->user->avatar)
                            : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($message->user->email))).'?s=80&d=identicon',
                        'attachment_url' => $message->attachment_url,
                        'attachment_name' => $message->attachment_name,
                        'is_image' => $message->isImage(),
                    ],
                ]);
            }

            return back()->with('success', __('Message sent'));

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Chat message send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', __('Failed to send message'));
        }
    }

    /**
     * Check for new messages (polling)
     */
    public function checkNewMessages(Request $request, ProductChat $chat)
    {
        // Check authorization
        if ($chat->customer_id !== Auth::id() && $chat->seller_id !== Auth::id()) {
            abort(403);
        }

        $afterId = $request->get('after', 0);
        $isSeller = $chat->seller_id === Auth::id();

        $messages = $chat->messages()
            ->where('id', '>', $afterId)
            ->with('user')
            ->get()
            ->map(function (ProductChatMessage $message) {
                return [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'is_seller' => $message->is_seller,
                    'user_name' => $message->user->name,
                    'user_avatar' => $message->user->avatar
                        ? asset('storage/'.$message->user->avatar)
                        : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($message->user->email))).'?s=80&d=identicon',
                    'created_at' => $message->created_at->diffForHumans(),
                    'attachment_url' => $message->attachment_url,
                    'attachment_name' => $message->attachment_name,
                    'is_image' => $message->isImage(),
                ];
            });

        // Mark new messages as read
        $chat->messages()
            ->where('id', '>', $afterId)
            ->where('is_seller', $isSeller ? false : true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * My chats list
     */
    public function index()
    {
        $chats = ProductChat::where('customer_id', Auth::id())
            ->with(['product', 'seller', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return view('product-chat.index', compact('chats'));
    }
}
