<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // POST /profile/accounts/switch
    public function switchAccount(\Illuminate\Http\Request $request)
    {
        $request->validate(['account_id' => 'required|integer']);

        $current = $request->user();
        $target = \App\Models\User::find($request->input('account_id'));

        if (! $target) {
            return response()->json(['success' => false, 'message' => 'Account not found'], 404);
        }

        // allow master <-> child, siblings, and impersonation return/switches
        $impersonator = session('impersonator_id');

        $isMasterToChild = ($target->parent_user_id !== null && $target->parent_user_id === $current->id);
        $isChildToMaster = ($current->parent_user_id !== null && $current->parent_user_id === $target->id);
        $isSameParent = ($current->parent_user_id !== null && $current->parent_user_id === $target->parent_user_id);

        // allow returning to the original account that started impersonation
        $isReturnToImpersonator = $impersonator && $target->id === $impersonator;
        // allow impersonator (original master) to switch between their children while impersonating
        $isImpersonatorSwitch = $impersonator && $current->id === $impersonator;

        if (! $isMasterToChild && ! $isChildToMaster && ! $isSameParent && ! $isReturnToImpersonator && ! $isImpersonatorSwitch) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized to switch to this account',
                'current_id' => $current->id,
                'current_parent' => $current->parent_user_id,
                'target_id' => $target->id,
                'target_parent' => $target->parent_user_id,
                'impersonator' => $impersonator,
            ], 403);
        }

        // if master->child and impersonator not set — set it
        if ($isMasterToChild && ! $impersonator) {
            session(['impersonator_id' => $current->id]);
        }

        \Illuminate\Support\Facades\Auth::loginUsingId($target->id, false);
        $request->session()->regenerate();
        session(['active_account_id' => $target->id]);

        return response()->json(['success' => true, 'message' => 'Switched to account', 'target_id' => $target->id]);
    }

    // Show form for creating a child account (master only)
    public function createChild(Request $request)
    {
        $master = $request->user();

        if (! $master->canCreateChild()) {
            return redirect()->route('profile.edit')->withErrors(['limit' => 'You cannot create more child accounts.']);
        }

        return view('profile.accounts.create_child', ['master' => $master]);
    }

    // Store child account (master only)
    public function storeChild(Request $request)
    {
        $master = $request->user();

        if (! $master->canCreateChild()) {
            return redirect()->back()->withErrors(['limit' => 'Child account limit reached.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8|confirmed',
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')],
        ]);

        $child = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'password' => Hash::make($data['password']),
            'parent_user_id' => $master->id,
        ]);

        return redirect()->route('profile.edit')->with('status', 'Child account created.');
    }

    // --- helper methods to sync session <-> DB for cart & wishlist ---

    protected function persistSessionCartToDb(int $userId): void
    {
        $sessionCart = session('cart', []);
        if (! is_array($sessionCart) || empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $entry) {
            // support multiple session formats
            $productId = $entry['product_id'] ?? $entry['id'] ?? null;
            $quantity = isset($entry['quantity']) ? (int) $entry['quantity'] : 1;
            if (! $productId) {
                continue;
            }

            $item = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
            if ($item) {
                $item->quantity = $item->quantity + $quantity;
                $item->save();
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => max(1, $quantity),
                ]);
            }
        }
    }

    protected function loadDbCartToSession(int $userId): void
    {
        $items = CartItem::with('product')->where('user_id', $userId)->get();
        $sessionCart = [];
        foreach ($items as $it) {
            $sessionCart[] = [
                'product_id' => $it->product_id,
                'quantity' => $it->quantity,
                'meta' => $it->meta ?? null,
            ];
        }
        session(['cart' => $sessionCart]);
    }

    protected function persistSessionWishlistToDb(int $userId): void
    {
        $sessionWishlist = session('wishlist', []);
        if (empty($sessionWishlist)) {
            return;
        }

        // if wishlist is map/object -> try to extract ids
        $ids = [];
        if (is_array($sessionWishlist)) {
            // numeric array of ids or array of arrays/objects
            foreach ($sessionWishlist as $v) {
                if (is_int($v) || ctype_digit((string) $v)) {
                    $ids[] = (int) $v;
                } elseif (is_array($v) && isset($v['product_id'])) {
                    $ids[] = (int) $v['product_id'];
                } elseif (is_object($v) && isset($v->product_id)) {
                    $ids[] = (int) $v->product_id;
                }
            }
        }

        foreach (array_unique($ids) as $pid) {
            WishlistItem::firstOrCreate(['user_id' => $userId, 'product_id' => $pid]);
        }
    }

    protected function loadDbWishlistToSession(int $userId): void
    {
        $ids = WishlistItem::where('user_id', $userId)->pluck('product_id')->toArray();
        session(['wishlist' => $ids]);
    }
}
