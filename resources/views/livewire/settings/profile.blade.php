<section class="w-full">
    @include('partials.settings-heading')

    <div class="max-w-2xl space-y-12 pb-12">
        <!-- Perfil -->
        <section>
            <flux:heading>{{ __('Perfil') }}</flux:heading>
            <flux:subheading>{{ __('Actualiza tu nombre y dirección de correo electrónico') }}</flux:subheading>

            <form wire:submit="updateProfileInformation" class="mt-6 w-full space-y-6">
                <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" :label="__('Correo Electrónico')" type="email" required autocomplete="email" />

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div>
                            <flux:text class="mt-4">
                                {{ __('Tu dirección de correo electrónico no está verificada.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                                </flux:link>
                            </flux:text>

                            @if (session('status') === 'verification-link-sent')
                                <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                    {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Rol (solo lectura) --}}
                <div>
                    <flux:input
                        :label="__('Rol')"
                        :value="auth()->user()->role ?? 'Sin rol asignado'"
                        type="text"
                        readonly
                        disabled
                        class="opacity-60 cursor-not-allowed"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">El rol de la cuenta no puede ser modificado desde aquí.</p>
                </div>

                {{-- Teléfono --}}
                <div>
                    <flux:input
                        wire:model="telefono"
                        :label="__('Teléfono')"
                        type="text"
                        inputmode="numeric"
                        placeholder="Ej: 04121234567"
                        autocomplete="tel"
                        maxlength="20"
                    />
                    @error('telefono')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Guardado.') }}
                    </x-action-message>
                </div>
            </form>
        </section>

        <flux:separator variant="subtle" />

        <!-- Contraseña -->
        <section>
            <flux:heading>{{ __('Actualizar contraseña') }}</flux:heading>
            <flux:subheading>{{ __('Asegúrate de que tu cuenta esté usando una contraseña larga y aleatoria para mantenerse segura') }}</flux:subheading>

        <form wire:submit="updatePassword" class="mt-6 space-y-6">
                <flux:input
                    wire:model="current_password"
                    :label="__('Contraseña actual')"
                    type="password"
                    required
                    autocomplete="current-password"
                    viewable
                />

                {{-- Nueva contraseña con indicador de fortaleza --}}
                <div>
                    <flux:input
                        wire:model="password"
                        id="settings-password-input"
                        :label="__('Nueva contraseña')"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        viewable
                    />
                    <div class="mt-2">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex-1 h-1.5 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div id="settings-strength-bar" class="h-full transition-all duration-300 ease-out" style="width: 0%"></div>
                            </div>
                            <span id="settings-strength-text" class="text-xs font-medium min-w-[80px] text-right"></span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Mínimo 8 caracteres. Puedes usar números y caracteres especiales.</p>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const pwInput = document.getElementById('settings-password-input');
                        const bar = document.getElementById('settings-strength-bar');
                        const label = document.getElementById('settings-strength-text');
                        if (!pwInput) return;
                        pwInput.addEventListener('input', function () {
                            let s = 0, p = this.value;
                            if (p.length === 0) { bar.style.width='0%'; label.textContent=''; return; }
                            if (p.length >= 8)  s += 25;
                            if (p.length >= 12) s += 15;
                            if (p.length >= 16) s += 10;
                            if (/[a-z]/.test(p)) s += 15;
                            if (/[A-Z]/.test(p)) s += 15;
                            if (/[0-9]/.test(p)) s += 10;
                            if (/[^a-zA-Z0-9]/.test(p)) s += 10;
                            s = Math.min(100, s);
                            let color, text;
                            if (s < 40)       { color='#ef4444'; text='Débil'; }
                            else if (s < 60)  { color='#f97316'; text='Regular'; }
                            else if (s < 80)  { color='#eab308'; text='Buena'; }
                            else              { color='#22c55e'; text='Fuerte'; }
                            bar.style.width = s + '%';
                            bar.style.backgroundColor = color;
                            label.textContent = text;
                            label.style.color = color;
                        });
                    });
                </script>

                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirmar contraseña')"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    viewable
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>

                    <x-action-message class="me-3" on="password-updated">
                        {{ __('Guardado.') }}
                    </x-action-message>
                </div>
            </form>
        </section>

        <flux:separator variant="subtle" />

        <!-- Apariencia -->
        <section>
            <flux:heading>{{ __('Apariencia') }}</flux:heading>
            <flux:subheading>{{ __('Actualiza la configuración de apariencia de tu cuenta') }}</flux:subheading>

            <div class="mt-6">
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Oscuro') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
                </flux:radio.group>
            </div>
        </section>

        <flux:separator variant="subtle" />
        
        @if(in_array(auth()->user()->role, ['Administrador', 'Encargado']))
            <!-- Mantenimiento del Sistema -->
            <section>
                <flux:heading class="text-red-500">{{ __('Mantenimiento del Sistema') }}</flux:heading>
                <flux:subheading>{{ __('Acciones críticas de administración. Ten cuidado, estas acciones son irreversibles.') }}</flux:subheading>

                <div class="mt-6 p-4 bg-red-50 dark:bg-red-950/20 rounded-xl border border-red-200 dark:border-red-900/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <flux:text class="font-semibold text-red-800 dark:text-red-400">{{ __('Reiniciar Datos Transaccionales') }}</flux:text>
                            <flux:text size="sm" class="text-red-700 dark:text-red-500/80">
                                {{ __('Elimina todas las ventas, órdenes de servicio, auditoría, servicios e inventario. Solo se mantienen los usuarios, clientes y vehículos.') }}
                            </flux:text>
                        </div>
                        
                        <flux:modal.trigger name="confirm-system-reset">
                            <flux:button variant="danger" size="sm">{{ __('Reiniciar Sistema') }}</flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>

                <flux:modal name="confirm-system-reset" variant="filled" class="max-w-md">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('¿Estás absolutamente seguro?') }}</flux:heading>
                            <flux:subheading>
                                {{ __('Esta acción eliminará permanentemente todos los datos de ventas, inventario y órdenes. No se puede deshacer.') }}
                            </flux:subheading>
                        </div>

                        <div class="flex gap-3 justify-end">
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                            </flux:modal.close>

                            <flux:button wire:click="resetSystem" variant="danger">{{ __('Sí, borrar todo') }}</flux:button>
                        </div>
                    </div>
                </flux:modal>
            </section>

            <flux:separator variant="subtle" />
        @endif

        <!-- Eliminar Cuenta -->
        <livewire:settings.delete-user-form />
    </div>
</section>
