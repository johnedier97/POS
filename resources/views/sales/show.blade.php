<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('sales.index') }}" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-indigo-600 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                        {{ __('Pedido #') . str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}
                    </h2>
                    <p class="text-[10px] uppercase font-black tracking-widest text-gray-400">Consulta de Transacción Histórica</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="bg-gray-900 hover:bg-black text-white font-black py-2.5 px-6 rounded-xl shadow-xl transition-all transform hover:scale-105 flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Ticket
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Content: Product List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
                            <h3 class="font-black text-gray-800 uppercase tracking-widest text-xs">Productos en este pedido</h3>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-black rounded-full uppercase tracking-tighter">{{ count($sale->details) }} Items</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <tr>
                                        <th class="px-8 py-4">Producto</th>
                                        <th class="px-6 py-4 text-center">Cantidad</th>
                                        <th class="px-6 py-4 text-right">Precio Unitario</th>
                                        <th class="px-8 py-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($sale->details as $detail)
                                    <tr class="group hover:bg-indigo-50/20 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-50">
                                                    @if($detail->product->image_path)
                                                        <img src="/storage/{{ $detail->product->image_path }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="text-xs font-black text-gray-300 uppercase">{{ substr($detail->product->name, 0, 2) }}</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="text-sm font-black text-gray-900 tracking-tight">{{ $detail->product->name }}</div>
                                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Código: {{ $detail->product->barcode ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="px-3 py-1 bg-gray-50 border border-gray-100 rounded-lg text-xs font-black text-gray-800">{{ $detail->quantity }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-right font-bold text-xs text-gray-600">
                                            $ {{ number_format($detail->price, 2) }}
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <div class="text-sm font-black text-gray-900">$ {{ number_format($detail->subtotal, 2) }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50/30">
                                    <tr>
                                        <td colspan="3" class="px-8 py-6 text-right text-xs font-black text-gray-400 uppercase tracking-widest tracking-tighter">Total Acumulado</td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="text-2xl font-black text-indigo-600 tracking-tighter">$ {{ number_format($sale->total, 2) }}</div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Payments Section -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h3 class="font-black text-gray-800 uppercase tracking-widest text-xs mb-6">Información de Pago</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($sale->payments as $payment)
                            <div class="flex items-center justify-between p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Medio de Pago</div>
                                        <div class="text-sm font-black text-emerald-900 tracking-tight">{{ $payment->paymentMethod->name ?? 'Indefinido' }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-black text-emerald-600 tracking-tighter">$ {{ number_format($payment->amount, 2) }}</div>
                                </div>
                            </div>
                            @endforeach

                            @if($sale->payments->isEmpty() && $sale->type === 'waste')
                                <div class="col-span-2 py-6 px-4 bg-orange-50 border border-orange-100 rounded-2xl flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p class="text-orange-700 font-bold text-sm">Esta transacción fue registrada como novedad/baja y no generó ingresos en caja.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Summary Info -->
                <div class="space-y-6">
                    <div class="bg-indigo-900 rounded-3xl shadow-xl p-8 text-white">
                        <h3 class="font-black uppercase tracking-widest text-xs text-indigo-300 mb-6 border-b border-indigo-800 pb-4">Detalles de Emisión</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Fecha y Hora</label>
                                <div class="font-bold text-sm tracking-tight">{{ $sale->created_at->translatedFormat('d F Y') }}</div>
                                <div class="font-mono text-xs text-indigo-300">{{ $sale->created_at->format('h:i:s A') }}</div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Local de Venta</label>
                                <div class="font-bold text-sm tracking-tight uppercase">{{ $sale->session->cashRegister->branch->name ?? 'Sede Principal' }}</div>
                                <div class="text-xs text-indigo-300">Caja: {{ $sale->session->cashRegister->name }}</div>
                                <div class="text-[9px] text-indigo-500 font-black mt-1 uppercase">Sesión de Caja #{{ $sale->session_id }}</div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Cajero Responsable</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-indigo-800 flex items-center justify-center text-[10px] font-black text-indigo-300">{{ substr($sale->user->name, 0,1) }}</div>
                                    <div class="font-bold text-sm tracking-tight">{{ $sale->user->name }}</div>
                                </div>
                                <div class="text-xs text-indigo-300 mt-1">{{ $sale->user->email }}</div>
                            </div>

                            <hr class="border-indigo-800">

                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Cliente / Receptor</label>
                                <div class="font-black text-lg tracking-tighter">{{ $sale->customer->name ?? 'CONSUMIDOR FINAL' }}</div>
                                @if($sale->customer)
                                    <div class="text-xs text-indigo-300">{{ $sale->customer->document ?? 'Sin documento' }}</div>
                                    <div class="text-xs text-indigo-300">{{ $sale->customer->email }}</div>
                                @else
                                    <div class="text-[10px] text-indigo-500 font-bold uppercase mt-1 italic tracking-widest">Venta sin datos fiscales</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($sale->is_electronic_invoiced)
                    <div class="bg-emerald-50 rounded-3xl border border-emerald-100 p-8">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h4 class="font-black text-emerald-800 uppercase tracking-widest text-xs">Venta Facturada</h4>
                        </div>
                        <p class="text-[10px] text-emerald-600 font-bold leading-relaxed mb-4 uppercase tracking-tighter">Esta venta cuenta con facturación electrónica emitida y reportada a los entes reguladores.</p>
                        <div class="bg-white rounded-xl p-3 border border-emerald-200 font-mono text-[9px] text-emerald-700 break-all">
                            ID FISCAL: {{ strtoupper(bin2hex($sale->id . 'token')) }}
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
