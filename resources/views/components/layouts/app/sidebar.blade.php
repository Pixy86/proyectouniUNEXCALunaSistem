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
    <body class="min-h-screen bg-white dark:bg-zinc-800" x-data="{ sidebarExpanded: false }">
        <flux:sidebar sticky stashable 
            @mouseenter="sidebarExpanded = true"
            @mouseleave="sidebarExpanded = false"
            class="sidebar-transition border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
            ::class="sidebarExpanded ? 'sidebar-expanded' : 'sidebar-collapsed'"
        >
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <div class="flex items-center h-8">
                <a href="{{ route('dashboard') }}" class="flex-1 flex items-center space-x-2 rtl:space-x-reverse min-w-0" wire:navigate>
                    <x-app-logo />
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="truncate font-semibold text-zinc-900 dark:text-white">SGIOSCI</span>
                </a>
            </div>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Inicio')" class="nav-group grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Dashboard') }}</span>
                    </flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Gestión')" class="nav-group grid">
                    <flux:navlist.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.index')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Clientes') }}</span>
                    </flux:navlist.item>
                    <flux:navlist.item icon="archive-box" :href="route('inventories.index')" :current="request()->routeIs('inventories.index')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Inventario') }}</span>
                    </flux:navlist.item>
                    <flux:navlist.item icon="wrench-screwdriver" :href="route('services.index')" :current="request()->routeIs('services.index')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Servicios') }}</span>
                    </flux:navlist.item>
                    @if(in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                        <flux:navlist.item icon="credit-card" :href="route('payment-methods.index')" :current="request()->routeIs('payment-methods.index')" wire:navigate>
                            <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Metodos de pago') }}</span>
                        </flux:navlist.item>
                    @endif
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Operaciones')" class="nav-group grid">
                    <flux:navlist.item icon="clipboard-document-list" :href="route('service-orders.index')" :current="request()->routeIs('service-orders.index')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Ordenes de servicio') }}</span>
                    </flux:navlist.item>
                    <flux:navlist.item icon="presentation-chart-line" :href="route('venta.index')" :current="request()->routeIs('venta.index')" wire:navigate>
                        <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Venta') }}</span>
                    </flux:navlist.item>
                    @if(in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                        <flux:navlist.item icon="shopping-cart" :href="route('sales.index')" :current="request()->routeIs('sales.index')" wire:navigate>
                            <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Historial de venta') }}</span>
                        </flux:navlist.item>
                    @endif
                </flux:navlist.group>

                @if(auth()->user()?->role === 'Administrador')
                    <flux:navlist.group :heading="__('Administración')" class="nav-group grid">
                        <flux:navlist.item icon="user" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>
                            <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Gestion de usuarios') }}</span>
                        </flux:navlist.item>
                        <flux:navlist.item icon="shield-check" :href="route('audit.index')" :current="request()->routeIs('audit.index')" wire:navigate>
                            <span x-show="sidebarExpanded" x-transition.opacity>{{ __('Auditoria') }}</span>
                        </flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    ::name="sidebarExpanded ? '{{ auth()->user()->name }}' : ''"
                    :initials="auth()->user()->initials()"
                    ::icon-trailing="sidebarExpanded ? 'chevron-up-down' : ''"
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
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Configuración') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Cerrar Sesión') }}
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
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Configuración') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Cerrar Sesión') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        
        @livewire('notifications')
        @filamentScripts
        @fluxScripts

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const dirtyModals = new Set();
                
                // Detectar cambios en inputs dentro de modales
                document.body.addEventListener('input', (e) => {
                    const modal = e.target.closest('[role="dialog"]');
                    if (modal) {
                        dirtyModals.add(modal);
                    }
                });

                // Limpiar estado sucio cuando se envía un formulario (asumiendo que guardar es exitoso o intencional)
                document.body.addEventListener('submit', (e) => {
                    const modal = e.target.closest('[role="dialog"]');
                    if (modal) {
                        dirtyModals.delete(modal);
                    }
                });

                // Función para verificar y confirmar cierre
                const checkModalClose = (e, modalElement) => {
                    if (!modalElement) return;
                    
                    // Si el modal está sucio y visible
                    if (dirtyModals.has(modalElement) && modalElement.offsetParent !== null) {
                        // Usar confirmación nativa del navegador que bloquea la ejecución
                        if (!confirm('¿Desea salir sin guardar los datos?')) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            return false;
                        } else {
                            dirtyModals.delete(modalElement);
                            return true;
                        }
                    }
                    return true;
                };

                // Interceptar clicks fuera del modal (Backdrop click)
                // Usamos captura (tercer argumento true) para interceptar antes que Alpine/Flux
                window.addEventListener('mousedown', (e) => {
                    const visibleModal = document.querySelector('[role="dialog"]:not([style*="display: none"])');
                    if (!visibleModal) return;

                    // Si el click es DENTRO del modal pero FUERA del panel de contenido
                    // Asumimos que el panel de contenido tiene alguna clase específica o es el primer hijo directo relevante
                    // Flux suele tener un backdrop que envuelve o está detrás.
                    // Si el target es el backdrop mismo...
                    
                    // Identificamos el panel interior (usualmente bg-white o similar)
                    const modalPanel = visibleModal.querySelector('[data-flux-modal-panel]') || 
                                     visibleModal.querySelector('.bg-white') || 
                                     visibleModal.querySelector('.bg-zinc-900');

                    if (modalPanel) {
                        // Si el click NO es en el panel y SÍ es en el contenedor del modal (backdrop)
                        if (!modalPanel.contains(e.target) && visibleModal.contains(e.target)) {
                            checkModalClose(e, visibleModal);
                        }
                    }
                }, true);

                // Interceptar tecla Escape
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        const visibleModal = document.querySelector('[role="dialog"]:not([style*="display: none"])');
                        if (visibleModal) {
                            checkModalClose(e, visibleModal);
                        }
                    }
                }, true);

                // Limpiar referencia de modales que ya no existen en el DOM
                setInterval(() => {
                    dirtyModals.forEach(modal => {
                        if (!document.body.contains(modal) || modal.style.display === 'none') {
                            dirtyModals.delete(modal);
                        }
                    });
                }, 1000);
                
                // Limpiar todo al navegar con livewire (SPA navigation)
                document.addEventListener('livewire:navigated', () => {
                    dirtyModals.clear();
                });
            });
        </script>
    </body>
</html>
