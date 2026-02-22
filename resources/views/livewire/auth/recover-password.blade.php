<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Recuperar contraseña')" 
        :description="!$emailVerified ? __('Ingresa tu correo para comenzar la recuperación') : __('Responde las preguntas de seguridad para ver tu contraseña')" 
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    @if ($errorMessage)
        <div class="rounded-lg border border-red-400/40 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-center text-sm text-red-700 dark:text-red-300">
            {{ $errorMessage }}
        </div>
    @endif

    @if (!$emailVerified)
        <form wire:submit="verifyEmail" class="flex flex-col gap-6">
            <!-- Email Address -->
            <flux:input
                wire:model="email"
                :label="__('Correo electrónico')"
                type="email"
                required
                autofocus
                placeholder="correo@ejemplo.com"
            />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Siguiente') }}
            </flux:button>
        </form>
    @else
        <form wire:submit="verifyAnswers" class="flex flex-col gap-6">
            <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                {{ __('Para el correo:') }} <strong>{{ $email }}</strong>
            </div>

            <!-- Question 1 -->
            <flux:input
                wire:model="security_answer_1"
                :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[1]"
                type="text"
                required
                placeholder="{{ __('Tu respuesta') }}"
            />

            <!-- Question 2 -->
            <flux:input
                wire:model="security_answer_2"
                :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[2]"
                type="text"
                required
                placeholder="{{ __('Tu respuesta') }}"
            />

            <!-- Question 3 -->
            <flux:input
                wire:model="security_answer_3"
                :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[3]"
                type="text"
                required
                placeholder="{{ __('Tu respuesta') }}"
            />

            <div class="flex gap-4">
                <flux:button variant="ghost" class="flex-1" wire:click="$set('emailVerified', false)">
                    {{ __('Volver') }}
                </flux:button>
                <flux:button variant="primary" type="submit" class="flex-1">
                    {{ __('Ver contraseña') }}
                </flux:button>
            </div>
        </form>
    @endif

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>{{ __('O, volver a') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('iniciar sesión') }}</flux:link>
    </div>

    <!-- Modal para mostrar la contraseña -->
    <flux:modal wire:model="showPasswordModal" class="min-w-[300px]" title="{{ __('Contraseña Recuperada') }}">
        <div class="space-y-6">
            <div class="text-center">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                    Tu contraseña actual es:
                </p>
                <div class="p-4 bg-zinc-100 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 font-mono text-xl text-zinc-900 dark:text-white break-all">
                    {{ $recoveredPassword }}
                </div>
                <p class="mt-4 text-xs text-zinc-500">
                    Al cerrar este mensaje serás redirigido al inicio de sesión.
                </p>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="primary" wire:click="closeModal">
                    {{ __('Cerrar y Volver al Login') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
