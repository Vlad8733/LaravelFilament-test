<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Request $r)
    {
        return view('profile.edit', ['user' => $r->user()]);
    }

    public function update(Request $r)
    {
        $u = $r->user();
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$u->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $u->name = $data['name'];
        $u->email = $data['email'];
        if (! empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        $u->save();

        return redirect()->route('profile.edit')->with('status', 'Account updated.');
    }

    public function updateAvatar(Request $r)
    {
        $u = $r->user();
        $r->validate(['avatar' => 'required|image|max:5120']);

        $path = $r->file('avatar')->store('avatars', 'public');
        if ($u->avatar && Storage::disk('public')->exists($u->avatar)) {
            Storage::disk('public')->delete($u->avatar);
        }
        $u->avatar = $path;
        $u->save();

        return redirect()->route('profile.edit')->with('status', 'Avatar updated.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($request->filled('current_password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Password is incorrect.']);
            }
        }

        Auth::logout();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Account deleted.');
    }
}
