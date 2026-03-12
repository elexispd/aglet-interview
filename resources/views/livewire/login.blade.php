<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $password = '';

    public $error = '';

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            return $this->redirectIntended('/');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4">
    <div class="bg-gray-800 p-8 rounded-lg shadow-2xl w-full max-w-md border border-gray-700">
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Welcome Back</h2>
        
        <form wire:submit="login" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-400 mb-1">Email Address</label>
                <input wire:model="email" type="email" id="email" class="w-full bg-gray-700 text-white rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('email') border-red-500 @enderror">
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-400 mb-1">Password</label>
                <input wire:model="password" type="password" id="password" class="w-full bg-gray-700 text-white rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('password') border-red-500 @enderror">
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="login" class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded transition duration-300">
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login">Signing in</span>
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Default User: <span class="text-gray-300">jointheteam@aglet.co.za</span></p>
            <p>Password: <span class="text-gray-300">@TeamAglet</span></p>
        </div>
    </div>
</div>
