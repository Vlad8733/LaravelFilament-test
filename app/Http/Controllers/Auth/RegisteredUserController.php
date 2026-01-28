<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => ['required', 'confirmed', Password::defaults()]]);
        $u = User::create(['name' => $d['name'], 'email' => $d['email'], 'password' => Hash::make($d['password'])]);
        Auth::login($u);

        return redirect()->intended(route('products.index'));
    }
}
