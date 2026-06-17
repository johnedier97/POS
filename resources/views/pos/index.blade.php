<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS | {{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; } /* Custom scrollbar */ ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; } </style>
</head>
<body class="antialiased overflow-hidden h-screen" x-data="posApp()">

    <!-- Header Navbar -->
    <header class="bg-indigo-900 border-b border-indigo-800 shadow-sm h-16 flex items-center justify-between px-6 z-10 relative">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-indigo-200 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-white tracking-wider">PUNTO DE VENTA</h1>
            <span class="bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide ml-4">
                {{ $session->cashRegister->name }}
            </span>
        </div>
        <div class="flex items-center gap-4 text-sm text-indigo-100 font-medium">
            <span>Operador: {{ Auth::user()->name }}</span>
            <span id="pos-clock" class="font-mono bg-indigo-800 px-3 py-1 rounded">--:--:--</span>
        </div>
    </header>

    <div class="flex h-[calc(100vh-4rem)]">
        
        <!-- Left Sub-panel: Products Grid -->
        <div class="w-8/12 bg-gray-50 flex flex-col border-r border-gray-200">
            <!-- Search & Filter Bar -->
            <div class="p-4 bg-white border-b border-gray-200 shadow-sm flex gap-4">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" class="block w-full pl-10 pr-3 py-3 border-gray-300 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition sm:text-sm" placeholder="Buscar por código de barras o nombre de producto...">
                </div>
                <button class="bg-indigo-100 text-indigo-700 px-4 py-3 rounded-xl font-bold border border-indigo-200 hover:bg-indigo-200 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <!-- Products Grid -->
            <div class="flex-grow p-4 overflow-y-auto">
                <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addProduct(product)" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-indigo-300 cursor-pointer transition-all transform active:scale-95 group flex flex-col h-48">
                            <div class="h-24 bg-gray-100 w-full relative">
                                <template x-if="product.image_path">
                                    <img :src="'/storage/' + product.image_path" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image_path">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                </template>
                                <div class="absolute inset-0 bg-indigo-900 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                <span x-show="product.is_composite" class="absolute top-2 right-2 bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Receta</span>
                            </div>
                            <div class="p-3 flex-grow flex flex-col justify-between">
                                <h3 class="text-sm font-bold text-gray-800 leading-tight line-clamp-2" x-text="product.name"></h3>
                                <p class="text-indigo-600 font-extrabold" x-text="'$' + formatMoney(product.price)"></p>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <p class="text-lg font-medium text-gray-500">No se encontraron productos coincidentes.</p>
                </div>
            </div>
        </div>

        <!-- Right Sub-panel: Cart & Checkout -->
        <div class="w-4/12 bg-white flex flex-col shadow-[0_0_15px_rgba(0,0,0,0.05)] z-10">
            
            <!-- Open Orders Tabs (Multi-cuentas) -->
            <div class="bg-indigo-50 pt-2 px-2 flex gap-1 overflow-x-auto border-b border-indigo-100 flex-shrink-0 hide-scrollbar">
                <template x-for="(tab, index) in tabs" :key="tab.id">
                    <div class="relative group">
                        <button @click="activeTabId = tab.id" 
                                class="px-4 py-2 text-sm font-bold rounded-t-lg transition-colors border-t border-l border-r border-transparent flex items-center gap-2 whitespace-nowrap"
                                :class="activeTabId === tab.id ? 'bg-white text-indigo-700 border-indigo-200' : 'text-gray-500 hover:bg-gray-200'">
                            <span x-text="tab.name"></span>
                            <span x-show="tab.cart.length > 0" class="bg-rose-500 text-white text-[10px] px-1.5 rounded-full" x-text="tab.cart.length"></span>
                        </button>
                        <!-- Close Tab Btn (only if > 1 tab) -->
                        <button x-show="tabs.length > 1" @click.stop="closeTab(index)" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-rose-500 transition">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </template>
                <button @click="addNewTab()" class="px-3 py-2 text-indigo-500 hover:text-indigo-800 hover:bg-indigo-100 rounded-t-lg font-bold" title="Nueva Cuenta">+</button>
            </div>

            <!-- Customer / Modifiers -->
            <div class="p-3 border-b border-gray-100 flex gap-2">
                <button @click="isCustomerModalOpen = true" class="flex-grow flex items-center justify-between bg-gray-50 hover:bg-gray-100 border border-gray-200 px-3 py-2 rounded-lg text-sm transition">
                    <span class="text-gray-600 font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span x-text="currentTab.customer_name ? currentTab.customer_name : 'Consumidor Final'"></span>
                    </span>
                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
                <button @click="currentTab.type = (currentTab.type === 'sale' ? 'waste' : 'sale')" class="px-3 py-2 rounded-lg text-sm font-bold border transition" :class="currentTab.type === 'sale' ? 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100' : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100'">
                    <span x-text="currentTab.type === 'sale' ? 'Venta' : 'Novedad'"></span>
                </button>
            </div>

            <!-- Cart Items list -->
            <div class="flex-grow overflow-y-auto bg-white p-3 space-y-2">
                <div x-show="currentTab.cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-16 h-16 mb-4 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
                    <p class="font-medium">Cuenta vacía</p>
                    <p class="text-sm">Agrega productos tocando la grilla</p>
                </div>
                
                <template x-for="(item, index) in currentTab.cart" :key="index">
                    <div class="flex items-center p-3 border border-gray-100 rounded-xl shadow-sm hover:border-indigo-200 group transition">
                        <!-- Image / Icon -->
                        <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center mr-3 overflow-hidden">
                             <template x-if="item.image_path">
                                  <img :src="'/storage/' + item.image_path" class="w-full h-full object-cover">
                             </template>
                             <template x-if="!item.image_path">
                                  <span class="text-xs font-bold text-gray-400" x-text="item.name.substring(0, 2).toUpperCase()"></span>
                             </template>
                        </div>
                        
                        <!-- Details -->
                        <div class="flex-grow">
                            <h4 class="text-sm font-bold text-gray-800 leading-tight" x-text="item.name"></h4>
                            <div class="text-xs text-gray-500 font-medium mt-1">
                                $<span x-text="formatMoney(item.price)"></span>
                            </div>
                        </div>
                        
                        <!-- Controls -->
                        <div class="flex flex-col items-end gap-2">
                            <p class="text-sm font-extrabold text-indigo-700">
                                $<span x-text="formatMoney(item.price * item.quantity)"></span>
                            </p>
                            <div class="flex items-center bg-gray-100 rounded-lg">
                                <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded-l-lg transition font-bold cursor-pointer select-none">&minus;</button>
                                <span class="w-8 text-center text-sm font-bold text-gray-800" x-text="item.quantity"></span>
                                <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded-r-lg transition font-bold cursor-pointer select-none">&plus;</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pre-Checkout Total -->
            <div class="p-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between text-gray-500 text-sm mb-1 font-medium">
                    <span>Subtotal</span>
                    <span>$<span x-text="formatMoney(cartTotal)"></span></span>
                </div>
                <div class="flex justify-between text-gray-500 text-sm mb-3 pb-3 border-b border-gray-200 border-dashed font-medium">
                    <span>Impuestos (Insumos)</span>
                    <span>$0.00</span>
                </div>
                <div class="flex justify-between items-end mb-4">
                    <span class="text-lg font-bold text-gray-800">Total</span>
                    <span class="text-3xl font-extrabold text-indigo-700">
                        $<span x-text="formatMoney(cartTotal)"></span>
                    </span>
                </div>
                <button @click="openPaymentModal" 
                        :disabled="currentTab.cart.length === 0" 
                        class="w-full flex items-center justify-center py-4 rounded-xl text-lg tracking-wide uppercase shadow-[0_4px_14px_0_rgba(79,70,229,0.39)] transition-all font-black transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="currentTab.type === 'sale' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-orange-500 hover:bg-orange-600 text-white'">
                    <span x-text="currentTab.type === 'sale' ? 'Cobrar Venta' : 'Registrar Novedad'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CUSTOMER PICKER MODAL                        -->
    <!-- ═══════════════════════════════════════════ -->
    <div x-show="isCustomerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60" @click="isCustomerModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" x-transition>
            <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold">Seleccionar Cliente</h3>
                <button @click="isCustomerModalOpen = false" class="text-indigo-300 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <input type="text" x-model="customerSearch" placeholder="Buscar cliente por nombre o documento..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm mb-3 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <!-- Consumidor Final -->
                <button @click="selectCustomer(null, null)" class="w-full text-left px-4 py-3 mb-1 rounded-lg hover:bg-indigo-50 border border-dashed border-indigo-200 flex items-center gap-3 transition">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-indigo-700 text-sm">Consumidor Final</div>
                        <div class="text-xs text-gray-400">Sin datos de cliente registrado</div>
                    </div>
                </button>
                <!-- Customer List -->
                <div class="space-y-1 max-h-64 overflow-y-auto mt-2">
                    <template x-for="c in filteredCustomers" :key="c.id">
                        <button @click="selectCustomer(c.id, c.name)" class="w-full text-left px-4 py-3 rounded-lg hover:bg-indigo-50 flex items-center gap-3 transition" :class="currentTab.customer_id === c.id ? 'bg-indigo-50 border border-indigo-300' : 'border border-transparent'">
                            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm" x-text="c.name.substring(0,2).toUpperCase()"></div>
                            <div>
                                <div class="font-bold text-gray-800 text-sm" x-text="c.name"></div>
                                <div class="text-xs text-gray-400" x-text="c.document || c.email || 'Sin documento'"></div>
                            </div>
                            <span x-show="currentTab.customer_id === c.id" class="ml-auto text-indigo-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </button>
                    </template>
                    <div x-show="filteredCustomers.length === 0" class="text-center py-6 text-gray-400 text-sm">No se encontraron clientes.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- SUCCESS MODAL (post-sale)                    -->
    <!-- ═══════════════════════════════════════════ -->
    <div x-show="isSuccessModalOpen" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-70"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden text-center" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
            <!-- Success Header -->
            <div :class="lastSaleType === 'waste' ? 'bg-amber-500' : 'bg-emerald-500'" class="px-6 pt-8 pb-10 relative">
                <div class="w-20 h-20 rounded-full bg-white bg-opacity-20 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h2 class="text-2xl font-black text-white" x-text="lastSaleType === 'waste' ? '¡Novedad Registrada!' : '¡Venta Exitosa!'"></h2>
                <p class="text-white text-opacity-80 text-sm mt-1" x-text="lastSaleType === 'sale' ? 'La transacción fue procesada correctamente.' : 'El evento fue almacenado sin cargo.'"></p>
            </div>
            <!-- Body -->
            <div class="px-6 py-5">
                <div class="bg-gray-50 rounded-xl p-4 mb-5">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Cobrado</div>
                    <div class="text-3xl font-black text-gray-900">$<span x-text="lastSaleTotal"></span></div>
                    <div x-show="Number(lastSaleChange) > 0" class="text-emerald-600 font-bold text-sm mt-1">Vuelto: $<span x-text="lastSaleChange"></span></div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <a :href="'/sales/' + lastSaleId + '/receipt'" target="_blank" class="flex flex-col items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-sm py-3 rounded-xl transition border border-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Ver Tirilla
                    </a>
                    <button @click="window.open('/sales/' + lastSaleId + '/receipt', '_blank')" class="flex flex-col items-center gap-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm py-3 rounded-xl transition border border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Imprimir
                    </button>
                </div>
                <button x-show="lastSaleType === 'sale'" @click="emitInvoiceFromModal()" :disabled="invoicing" class="w-full py-3 rounded-xl bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold text-sm transition border border-indigo-200 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span x-text="invoicing ? 'Emitiendo...' : 'Emitir Factura Electrónica'"></span>
                </button>
                <div x-show="invoiceStatus" class="mt-2 text-[10px] font-mono text-indigo-500 break-all" x-text="invoiceStatus"></div>
            </div>
            <div class="px-6 pb-6">
                <button @click="closeSuccessModal()" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-base tracking-wide transition">
                    Nueva Venta ➔
                </button>
            </div>
        </div>
    </div>

    <!-- Payments Modal -->
    <div x-show="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="isPaymentModalOpen = false" x-show="isPaymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 overflow-hidden transform transition-all" x-show="isPaymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="bg-indigo-900 px-6 py-4 border-b border-indigo-800 flex justify-between items-center text-white">
                <h3 class="text-xl font-bold uppercase tracking-wider" id="modal-title">Confirmar Cobro</h3>
                <button @click="isPaymentModalOpen = false" class="text-indigo-300 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 flex grid grid-cols-2 gap-8">
                <!-- Left: Multi-Pago UI -->
                <div>
                    <h4 class="text-gray-500 text-sm font-bold uppercase mb-4">Métodos de Pago Acumulables</h4>
                    
                    <!-- Added Payments list -->
                    <div class="space-y-2 mb-4">
                        <template x-for="(pay, index) in currentTab.payments" :key="index">
                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div>
                                    <span class="font-bold text-gray-800 text-sm" x-text="getPaymentMethodName(pay.payment_method_id)"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">$<span x-text="formatMoney(pay.amount)"></span></span>
                                    <button @click="removePayment(index)" class="text-rose-500 hover:text-rose-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <p x-show="currentTab.payments.length === 0" class="text-xs text-gray-400 italic">No se han registrado abonos todavía.</p>
                    </div>

                    <!-- Add Payment form -->
                    <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100" x-show="balanceDue > 0">
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1">Método</label>
                            <select x-model="selectedPaymentMethod" class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-for="method in paymentMethods" :key="method.id">
                                    <option :value="method.id" x-text="method.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1">Monto a abonar</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-medium sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" x-model="paymentInputAmount" @keyup.enter="addPayment()"
                                    class="w-full pl-7 rounded-lg border-gray-300 text-lg font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                            </div>
                        </div>
                        <button @click="addPayment()" class="w-full bg-indigo-200 hover:bg-indigo-300 text-indigo-800 font-bold py-2 px-4 rounded-lg transition text-sm">
                            Registrar Abono
                        </button>
                    </div>

                </div>

                <!-- Right: Summary -->
                <div class="border-l border-gray-100 pl-8 flex flex-col justify-between">
                    <div>
                        <h4 class="text-gray-500 text-sm font-bold uppercase mb-4 text-right">Resumen FInal</h4>
                        
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600 font-medium text-sm">Subtotal Venta:</span>
                            <span class="text-gray-800 font-bold">$<span x-text="formatMoney(cartTotal)"></span></span>
                        </div>
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                            <span class="text-gray-600 font-medium text-sm">Pagos Registrados:</span>
                            <span class="text-emerald-600 font-bold">-$<span x-text="formatMoney(totalPaid)"></span></span>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">SALDO PENDIENTE (A COBRAR)</p>
                            <div class="text-4xl font-extrabold" :class="balanceDue > 0 ? 'text-rose-600' : (balanceDue < 0 ? 'text-emerald-500' : 'text-gray-900')">
                                $<span x-text="formatMoney(balanceDue)"></span>
                            </div>
                            <p x-show="balanceDue < 0" class="text-emerald-600 font-bold text-sm mt-1">¡VUELTO PARA EL CLIENTE!</p>
                            <p x-show="balanceDue === 0 && cartTotal > 0" class="text-gray-500 font-bold text-sm mt-1">CUENTA SALDADA EQUITATIVAMENTE</p>
                        </div>
                    </div>

                    <button @click="processFinalSale()" 
                        :disabled="balanceDue > 0 && currentTab.type === 'sale'"
                        class="mt-8 w-full py-4 rounded-xl text-lg font-black uppercase text-white shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="balanceDue <= 0 || currentTab.type === 'waste' ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-gray-300'">
                        <span x-text="processing ? 'Procesando...' : (currentTab.type === 'waste' ? 'Confirmar Novedad' : 'FINALIZAR TRANSACCIÓN')"></span>
                    </button>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Start live clock
        setInterval(() => { document.getElementById('pos-clock').innerText = new Date().toLocaleTimeString(); }, 1000);

        function posApp() {
            return {
                products: @json($products),
                paymentMethods: @json($paymentMethods),
                customers: @json($customers),

                tabs: [ { id: 1, name: 'Cuenta 1', cart: [], type: 'sale', payments: [], customer_id: null, customer_name: null } ],
                activeTabId: 1,
                tabCounter: 1,

                searchQuery: '',
                customerSearch: '',

                isPaymentModalOpen: false,
                isCustomerModalOpen: false,
                isSuccessModalOpen: false,

                selectedPaymentMethod: @json($paymentMethods->first() ? $paymentMethods->first()->id : ''),
                paymentInputAmount: 0,
                processing: false,

                // Last sale state for success modal
                lastSaleId: null,
                lastSaleTotal: '0.00',
                lastSaleChange: '0.00',
                lastSaleType: 'sale',
                invoicing: false,
                invoiceStatus: '',

                get currentTab() { return this.tabs.find(t => t.id === this.activeTabId); },
                
                get cartTotal() { 
                    if (!this.currentTab) return 0;
                    return this.currentTab.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0); 
                },

                get filteredProducts() {
                    if (this.searchQuery === '') return this.products;
                    return this.products.filter(p => p.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                },

                formatMoney(amount) { return Number(amount).toFixed(2); },

                // Cart Actions
                addProduct(product) {
                    let existing = this.currentTab.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.currentTab.cart.push({
                            product_id: product.id,
                            name: product.name,
                            price: product.price,
                            cost: product.cost,
                            image_path: product.image_path,
                            quantity: 1
                        });
                    }
                },

                updateQty(index, change) {
                    let item = this.currentTab.cart[index];
                    item.quantity += change;
                    if (item.quantity <= 0) {
                        this.currentTab.cart.splice(index, 1);
                    }
                },

                // Customer Actions
                get filteredCustomers() {
                    if (!this.customerSearch) return this.customers;
                    const q = this.customerSearch.toLowerCase();
                    return this.customers.filter(c =>
                        c.name.toLowerCase().includes(q) ||
                        (c.document && c.document.toLowerCase().includes(q)) ||
                        (c.email && c.email.toLowerCase().includes(q))
                    );
                },

                selectCustomer(id, name) {
                    this.currentTab.customer_id = id;
                    this.currentTab.customer_name = name;
                    this.isCustomerModalOpen = false;
                    this.customerSearch = '';
                },

                // Tabs Actions
                addNewTab() {
                    this.tabCounter++;
                    let newId = this.tabCounter;
                    this.tabs.push({ id: newId, name: 'Cuenta ' + newId, cart: [], type: 'sale', payments: [], customer_id: null, customer_name: null });
                    this.activeTabId = newId;
                },

                closeTab(index) {
                    let t = this.tabs[index];
                    if(t.cart.length > 0 && !confirm('La cuenta tiene artículos. ¿Seguro que deseas cerrarla?')) return;
                    this.tabs.splice(index, 1);
                    if (this.activeTabId === t.id) {
                        this.activeTabId = this.tabs[this.tabs.length - 1].id;
                    }
                },

                closeSuccessModal() {
                    this.isSuccessModalOpen = false;
                    this.isPaymentModalOpen = false;
                    this.currentTab.cart = [];
                    this.currentTab.payments = [];
                    this.currentTab.type = 'sale';
                    this.currentTab.customer_id = null;
                    this.currentTab.customer_name = null;
                    this.invoicing = false;
                    this.invoiceStatus = '';
                },

                async emitInvoiceFromModal() {
                    if (this.invoicing || !this.lastSaleId) return;
                    this.invoicing = true;
                    this.invoiceStatus = 'Iniciando proceso...';

                    try {
                        const response = await fetch(`/sales/${this.lastSaleId}/invoice-mock`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.invoiceStatus = `Factura: ${result.invoice_no}\nCUFE: ${result.cufe}`;
                        } else {
                            this.invoiceStatus = 'Error: ' + (result.error || 'No se pudo emitir');
                        }
                    } catch (error) {
                        this.invoiceStatus = 'Error de red al emitir factura.';
                        console.error(error);
                    } finally {
                        this.invoicing = false;
                    }
                },

                // Payment Logic
                openPaymentModal() {
                    this.isPaymentModalOpen = true;
                    // Auto-fill remaining balance
                    this.paymentInputAmount = this.balanceDue > 0 ? parseFloat(this.balanceDue).toFixed(2) : 0;
                },

                get totalPaid() {
                    if(!this.currentTab) return 0;
                    return this.currentTab.payments.reduce((sum, p) => sum + Number(p.amount), 0);
                },

                get balanceDue() {
                    return this.cartTotal - this.totalPaid;
                },

                getPaymentMethodName(id) {
                    let m = this.paymentMethods.find(m => m.id == id);
                    return m ? m.name : 'Varios';
                },

                addPayment() {
                    let amount = Number(this.paymentInputAmount);
                    if(amount <= 0 || !this.selectedPaymentMethod) return;
                    
                    this.currentTab.payments.push({
                        payment_method_id: this.selectedPaymentMethod,
                        amount: amount
                    });
                    
                    this.paymentInputAmount = this.balanceDue > 0 ? parseFloat(this.balanceDue).toFixed(2) : 0;
                },

                removePayment(index) {
                    this.currentTab.payments.splice(index, 1);
                    this.paymentInputAmount = this.balanceDue > 0 ? parseFloat(this.balanceDue).toFixed(2) : 0;
                },

                async processFinalSale() {
                    if(this.processing) return;
                    
                    // Allow save if waste, or if sale and fully paid
                    if (this.currentTab.type === 'sale' && this.balanceDue > 0) return;

                    this.processing = true;

                    const payload = {
                        sale: {
                            customer_id: this.currentTab.customer_id,
                            type: this.currentTab.type,
                            total: this.cartTotal,
                            is_electronic_invoiced: false
                        },
                        items: this.currentTab.cart,
                        payments: this.currentTab.payments
                    };

                    try {
                        const response = await fetch("{{ route('pos.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Calculate change before clearing
                            const totalPaidAmt = this.currentTab.payments.reduce((s, p) => s + Number(p.amount), 0);
                            const changeAmt = Math.max(0, totalPaidAmt - this.cartTotal);

                            // Populate success modal data
                            this.lastSaleId    = result.sale_id;
                            this.lastSaleTotal = this.formatMoney(this.cartTotal);
                            this.lastSaleChange = this.formatMoney(changeAmt);
                            this.lastSaleType  = this.currentTab.type;

                            // Show success modal (closeSuccessModal() clears cart)
                            this.isPaymentModalOpen = false;
                            this.isSuccessModalOpen = true;
                        } else {
                            alert('Hubo un error al procesar. Verifica la conexión.\n' + (result.error || ''));
                        }
                    } catch (error) {
                        alert('Error de red al procesar venta.');
                        console.error(error);
                    } finally {
                        this.processing = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
