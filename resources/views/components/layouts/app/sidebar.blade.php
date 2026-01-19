<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
        @filamentStyles 

        </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Maestros y Recursos')" class="grid">
                    <flux:navlist.item icon="archive-box" :href="route('inventories.index')" :current="request()->routeIs('inventories.index')" wire:navigate>{{ __('Manage Inventories') }}</flux:navlist.item>
                    <flux:navlist.item icon="wrench-screwdriver" :href="route('services.index')" :current="request()->routeIs('services.index')" wire:navigate>{{ __('Manage Services') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Registro y Venta')" class="grid">
                    <flux:navlist.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.index')" wire:navigate>{{ __('Manage Customers') }}</flux:navlist.item>
                    @if(auth()->user()?->role === 'Administrador')
                        <flux:navlist.item icon="credit-card" :href="route('payment-methods.index')" :current="request()->routeIs('payment-methods.index')" wire:navigate>{{ __('Manage Payment Methods') }}</flux:navlist.item>
                    @endif
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Operaciones')" class="grid">
                    <flux:navlist.item icon="presentation-chart-line" :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>{{ __('Punto de Venta (POS)') }}</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('service-orders.index')" :current="request()->routeIs('service-orders.index')" wire:navigate>{{ __('Órdenes de Servicio') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Ventas')" class="grid">
                    <flux:navlist.item icon="shopping-cart" :href="route('sales.index')" :current="request()->routeIs('sales.index')" wire:navigate>{{ __('Historial de Ventas') }}</flux:navlist.item>
                </flux:navlist.group>

                @if(auth()->user()?->role === 'Administrador')
                    <flux:navlist.group :heading="__('Administración y Control')" class="grid">
                        <flux:navlist.item icon="user" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                        <flux:navlist.item icon="shield-check" :href="route('audit.index')" :current="request()->routeIs('audit.index')" wire:navigate>{{ __('Auditoría') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        
        @livewire('notifications')
        @filamentScripts
        @fluxScripts
    </body>
</html>
