<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Resumen de Ventas y Rendimiento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @php
                $activeSession = Auth::user()->sessions()->where('status', 'open')->latest()->first();
            @endphp

            @if(session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6" role="alert"><p>{{ session('success') }}</p></div>
            @endif
            @if(session('info'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert"><p>{{ session('info') }}</p></div>
            @endif
            @if(session('error'))
                <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6" role="alert"><p>{{ session('error') }}</p></div>
            @endif

            <!-- Shift Status Banner -->
            @if(!$activeSession)
                <div class="bg-white rounded-xl shadow-lg border-l-4 border-amber-500 p-6 mb-8 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">¡Caja Cerrada!</h2>
                        <p class="text-gray-600">No tienes ningun turno activo. Debes abrir tu caja para comenzar a procesar ventas.</p>
                    </div>
                    <a href="{{ route('sessions.create') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-lg shadow-md transition text-lg">
                        Iniciar Turno de POS
                    </a>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg border-l-4 border-emerald-500 p-6 mb-8 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-emerald-700">Turno Activo: {{ $activeSession->cashRegister->name }}</h2>
                        <p class="text-gray-500 text-sm">Abierto el: {{ $activeSession->opened_at->format('d/m/Y h:i A') }} • Base: ${{ number_format($activeSession->initial_balance, 2) }}</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('pos.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition flex items-center gap-2">
                            Ir al POS
                        </a>
                        <a href="{{ route('sessions.edit', $activeSession) }}" class="bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold py-3 px-6 rounded-lg shadow-sm border border-rose-200 transition">
                            Cerrar Caja
                        </a>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Tarjeta Resumen 1 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-indigo-500 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Ventas del Día</h3>
                    <p class="text-3xl font-extrabold text-gray-800 mt-2">$ {{ number_format($todaySales, 2) }}</p>
                </div>
                <!-- Tarjeta Resumen 2 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-rose-500 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Alerta Inventario</h3>
                    <p class="text-3xl font-extrabold text-gray-800 mt-2">{{ $inventoryAlerts }} <span class="text-sm font-medium text-gray-400">productos críticos</span></p>
                </div>
                <!-- Tarjeta Resumen 3 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Cajas Activas</h3>
                    <p class="text-3xl font-extrabold text-gray-800 mt-2">{{ $activeRegisters }} <span class="text-sm font-medium text-gray-400">en turno</span></p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Ventas del Mes Actual</h3>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-semibold rounded-full">Proyección</span>
                    </div>
                    <!-- ChartJS Container -->
                    <div class="relative h-96 w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)'); // Indigo
            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
            
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($days),
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: @json($salesData),
                        borderColor: '#4f46e5', // indigo-600
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937', 
                            titleFont: { size: 13, family: 'Inter' },
                            bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '$ ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            ticks: { 
                                font: { family: 'Inter', size: 12 }, 
                                color: '#6b7280',
                                callback: function(value) { return '$' + value/1000 + 'k'; }
                            }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { family: 'Inter', size: 12 }, color: '#6b7280' }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
