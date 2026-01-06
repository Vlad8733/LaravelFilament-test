@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ __('Two-Factor Authentication') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Add additional security to your account using two-factor authentication.') }}
            </p>
        </div>

        <div class="px-6 py-6">
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if ($enabled)
                {{-- 2FA is enabled --}}
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Enabled') }}</h3>
                        <p class="text-sm text-gray-600">{{ __('Two-factor authentication is currently enabled.') }}</p>
                    </div>
                </div>

                {{-- Recovery Codes --}}
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-900 mb-2">{{ __('Recovery Codes') }}</h4>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('Store these recovery codes in a secure location. They can be used to recover access to your account if you lose your authenticator device.') }}
                    </p>
                    <div class="bg-gray-100 rounded-lg p-4 font-mono text-sm grid grid-cols-2 gap-2">
                        @foreach ($recoveryCodes as $code)
                            <div class="text-gray-700">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>

                {{-- Regenerate Codes --}}
                <form action="{{ route('two-factor.regenerate') }}" method="POST" class="mb-6">
                    @csrf
                    <div class="mb-4">
                        <label for="regenerate_password" class="block text-sm font-medium text-gray-700">
                            {{ __('Confirm Password to Regenerate Codes') }}
                        </label>
                        <input type="password" 
                               name="password" 
                               id="regenerate_password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        {{ __('Regenerate Recovery Codes') }}
                    </button>
                </form>

                {{-- Disable 2FA --}}
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-medium text-red-600 mb-2">{{ __('Disable Two-Factor Authentication') }}</h4>
                    <form action="{{ route('two-factor.disable') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="mb-4">
                            <label for="disable_password" class="block text-sm font-medium text-gray-700">
                                {{ __('Confirm Password') }}
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="disable_password"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                   required>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            {{ __('Disable 2FA') }}
                        </button>
                    </form>
                </div>

            @else
                {{-- 2FA is not enabled --}}
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Not Enabled') }}</h3>
                        <p class="text-sm text-gray-600">{{ __('Two-factor authentication adds an extra layer of security to your account.') }}</p>
                    </div>
                </div>

                <a href="{{ route('two-factor.setup') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    {{ __('Enable Two-Factor Authentication') }}
                </a>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
            &larr; {{ __('Back to Profile') }}
        </a>
    </div>
</div>
@endsection
