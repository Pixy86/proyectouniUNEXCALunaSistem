<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Crear una cuenta')" :description="__('Ingresa tus datos abajo para crear tu cuenta')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <div>
                <flux:input
                    name="name"
                    :label="__('Nombre')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Nombre completo')"
                    pattern="[\pL\s\-']+"
                    title="Solo se permiten letras, espacios, guiones y apóstrofos"
                />
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Solo letras. No se permiten números ni caracteres especiales.
                </p>
            </div>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electrónico')"
                type="email"
                required
                autocomplete="email"
                placeholder="correo@ejemplo.com"
            />

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    id="password-input"
                    :label="__('Contraseña')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Contraseña')"
                    viewable
                />
                
                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="flex-1 h-1.5 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div id="password-strength-bar" class="h-full transition-all duration-300 ease-out" style="width: 0%"></div>
                        </div>
                        <span id="password-strength-text" class="text-xs font-medium min-w-[80px] text-right"></span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Mínimo 8 caracteres. Puedes usar números y caracteres especiales.
                    </p>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const passwordInput = document.getElementById('password-input');
                    const strengthBar = document.getElementById('password-strength-bar');
                    const strengthText = document.getElementById('password-strength-text');

                    passwordInput.addEventListener('input', function() {
                        const password = this.value;
                        const strength = calculatePasswordStrength(password);
                        updateStrengthIndicator(strength);
                    });

                    function calculatePasswordStrength(password) {
                        let strength = 0;
                        
                        if (password.length === 0) return 0;
                        
                        // Length check
                        if (password.length >= 8) strength += 25;
                        if (password.length >= 12) strength += 15;
                        if (password.length >= 16) strength += 10;
                        
                        // Character variety checks
                        if (/[a-z]/.test(password)) strength += 15; // Lowercase
                        if (/[A-Z]/.test(password)) strength += 15; // Uppercase
                        if (/[0-9]/.test(password)) strength += 10; // Numbers
                        if (/[^a-zA-Z0-9]/.test(password)) strength += 10; // Special characters
                        
                        return Math.min(100, strength);
                    }

                    function updateStrengthIndicator(strength) {
                        let color, text;
                        
                        if (strength === 0) {
                            color = 'transparent';
                            text = '';
                        } else if (strength < 40) {
                            color = '#ef4444'; // Red
                            text = 'Débil';
                        } else if (strength < 60) {
                            color = '#f97316'; // Orange
                            text = 'Regular';
                        } else if (strength < 80) {
                            color = '#eab308'; // Yellow
                            text = 'Buena';
                        } else {
                            color = '#22c55e'; // Green
                            text = 'Fuerte';
                        }
                        
                        strengthBar.style.width = strength + '%';
                        strengthBar.style.backgroundColor = color;
                        strengthText.textContent = text;
                        strengthText.style.color = color;
                    }
                });
            </script>

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmar contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirmar contraseña')"
                viewable
            />

            <!-- Security Questions -->
            <div class="space-y-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                    {{ __('Preguntas de Seguridad para Recuperación') }}
                </h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Estas respuestas te permitirán recuperar tu contraseña si la olvidas. Solo se permiten letras.') }}
                </p>

                <!-- Question 1 -->
                <div>
                    <flux:input
                        name="security_answer_1"
                        :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[1]"
                        type="text"
                        required
                        placeholder="{{ __('Tu respuesta') }}"
                        pattern="[\pL\s]+"
                        title="Solo se permiten letras y espacios"
                    />
                </div>

                <!-- Question 2 -->
                <div>
                    <flux:input
                        name="security_answer_2"
                        :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[2]"
                        type="text"
                        required
                        placeholder="{{ __('Tu respuesta') }}"
                        pattern="[\pL\s]+"
                        title="Solo se permiten letras y espacios"
                    />
                </div>

                <!-- Question 3 -->
                <div>
                    <flux:input
                        name="security_answer_3"
                        :label="\App\Livewire\Auth\RecoverPassword::getSecurityQuestions()[3]"
                        type="text"
                        required
                        placeholder="{{ __('Tu respuesta') }}"
                        pattern="[\pL\s]+"
                        title="Solo se permiten letras y espacios"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Crear cuenta') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('¿Ya tienes una cuenta?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Iniciar sesión') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
