@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('Two-Factor Authentication') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('Enter the code from your authenticator app') }}
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('two-factor.verify') }}" method="POST">
            @csrf

            <div>
                <label for="code" class="sr-only">{{ __('Authentication Code') }}</label>
                <input id="code" 
                       name="code" 
                       type="text" 
                       inputmode="numeric" 
                       autocomplete="one-time-code"
                       required 
                       class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-amber-500 focus:border-amber-500 focus:z-10 text-center text-2xl tracking-widest @error('code') border-red-500 @enderror" 
                       placeholder="000000"
                       maxlength="21"
                       autofocus>

                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                    {{ __('Verify') }}
                </button>
            </div>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                {{ __('Lost your device?') }}
                <a href="#" onclick="document.getElementById('recovery-info').classList.toggle('hidden')" class="font-medium text-amber-600 hover:text-amber-500">
                    {{ __('Use a recovery code') }}
                </a>
            </p>
            <div id="recovery-info" class="hidden mt-4 p-4 bg-gray-100 rounded-lg text-left">
                <p class="text-sm text-gray-700">
                    {{ __('Enter one of your recovery codes in the field above. Recovery codes are single-use.') }}
                </p>
            </div>
        </div>

        <div class="text-center">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                    {{ __('Sign out and try again') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
