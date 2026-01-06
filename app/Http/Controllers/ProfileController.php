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

    // Показывает страницу профиля
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    // Обновление аккаунта: name, email, password
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Account updated.');
    }

    // Отдельный маршрут для загрузки/обновления аватара
    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'avatar' => 'required|image|max:5120', // 5MB
        ]);

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        // удалить старый аватар, если есть
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $path;
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Avatar updated.');
    }

    // Удаление аккаунта (удаляет пользователя и логаутит)
    public function destroy(Request $request)
    {
        $user = $request->user();

        // опционально: требовать подтверждение пароля
        if ($request->filled('current_password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Password is incorrect.']);
            }
        }

        Auth::logout();

        // удалить аватар из storage
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Account deleted.');
    }
}
