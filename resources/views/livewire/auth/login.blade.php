<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Inicia sesión en tu cuenta')" :description="__('Ingresa tu correo electrónico y contraseña abajo para iniciar sesión')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @if (session('status') === 'password-changed')
            <div class="rounded-lg border border-emerald-400/40 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-center text-sm text-emerald-700 dark:text-emerald-300">
                ✅ Tu contraseña fue actualizada correctamente. Por favor inicia sesión con tu nueva contraseña.
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electrónico')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@ejemplo.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Contraseña')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Contraseña')"
                    viewable
                />

                <flux:link class="absolute top-0 text-sm end-0" :href="route('password.recover')" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </flux:link>
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Recuérdame')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Iniciar sesión') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('¿No tienes una cuenta?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Registrarse') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
