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

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
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
                />
                <flux:input
                    wire:model="password"
                    :label="__('Nueva contraseña')"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirmar contraseña')"
                    type="password"
                    required
                    autocomplete="new-password"
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

        <!-- Eliminar Cuenta -->
        <livewire:settings.delete-user-form />
    </div>
</section>
