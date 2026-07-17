<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }} | {{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800&display=swap" rel="stylesheet">
    <style>
        /* ── General Reset & Fonts ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
        }

        /* ── Action Bar (web only) ── */
        .action-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            width: 100%;
            max-width: 420px;
        }
        .btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #fff; color: #374151; border: 2px solid #e5e7eb; }
        .btn-secondary:hover { border-color: #4f46e5; color: #4f46e5; }
        .btn-green { background: #059669; color: #fff; }
        .btn-green:hover { background: #047857; }
        .btn-green:disabled { background: #6ee7b7; cursor: not-allowed; }
        .btn-orange { background: #d97706; color: #fff; }

        /* ── Receipt Card ── */
        .receipt {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 1.25rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        /* ── Receipt Header ── */
        .receipt-header {
            background: #1e1b4b;
            color: #fff;
            padding: 2rem 1.5rem 2.5rem;
            text-align: center;
            position: relative;
        }
        .receipt-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 30px;
            background: #fff;
            clip-path: ellipse(55% 100% at 50% 100%);
        }
        .receipt-header .logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .receipt-header .subtitle {
            font-size: 0.78rem;
            color: #a5b4fc;
            margin-top: 0.25rem;
        }
        .receipt-type-badge {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .badge-sale   { background: #10b981; color: #fff; }
        .badge-waste  { background: #f59e0b; color: #fff; }
        .badge-consumo { background: #f59e0b; color: #fff; }

        /* ── Meta Info ── */
        .receipt-meta {
            padding: 1.75rem 1.5rem 1rem;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem 1rem;
            font-size: 0.8rem;
        }
        .meta-item .label { color: #9ca3af; font-weight: 600; text-transform: uppercase; font-size: 0.68rem; }
        .meta-item .value { color: #1f2937; font-weight: 700; margin-top: 0.1rem; }

        /* ── Divider ── */
        .dashed { border: none; border-top: 2px dashed #e5e7eb; margin: 0 1.5rem; }

        /* ── Items ── */
        .receipt-items { padding: 1.25rem 1.5rem; }
        .item-header {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 0.5rem;
            font-size: 0.68rem;
            font-weight: 800;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 0.5rem;
        }
        .item-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 0.5rem;
            align-items: baseline;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f9fafb;
        }
        .item-name { font-size: 0.85rem; font-weight: 700; color: #1f2937; }
        .item-qty  { font-size: 0.8rem; color: #6b7280; text-align: center; min-width: 2.5rem;}
        .item-total{ font-size: 0.85rem; font-weight: 800; color: #4f46e5; text-align: right; min-width: 4rem;}

        /* ── Totals ── */
        .receipt-totals { padding: 1rem 1.5rem 1.25rem; }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.4rem;
        }
        .total-row.grand {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1f2937;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 2px solid #e5e7eb;
        }
        .total-row.grand .amount { color: #4f46e5; }

        /* ── Payments ── */
        .receipt-payments { padding: 0 1.5rem 1.25rem; }
        .pay-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            background: #f0fdf4;
            border-radius: 0.5rem;
            padding: 0.45rem 0.75rem;
            margin-bottom: 0.35rem;
            font-weight: 600;
            color: #065f46;
        }

        /* ── Electronic Invoice Section ── */
        .fe-section {
            margin: 0 1.5rem 1.25rem;
            padding: 1rem;
            border-radius: 0.875rem;
            border: 2px solid #e0e7ff;
            background: #eef2ff;
            text-align: center;
        }
        .fe-section .fe-title { font-size: 0.75rem; font-weight: 800; color: #4338ca; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; }
        .fe-issued .cufe-label { font-size: 0.68rem; color: #6b7280; font-weight: 600; }
        .fe-issued .cufe-value { font-size: 0.72rem; font-weight: 800; color: #312e81; word-break: break-all; margin-top: 0.2rem; letter-spacing: 0.04em; }
        .fe-issued .fe-no { font-size: 0.9rem; font-weight: 800; color: #4f46e5; margin-bottom: 0.5rem; }
        .fe-pending-btn {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1.25rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 0.625rem;
            font-weight: 800;
            font-size: 0.82rem;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .fe-pending-btn:hover { background: #4338ca; }
        .fe-pending-btn:disabled { background: #a5b4fc; cursor: not-allowed; }

        /* ── Footer ── */
        .receipt-footer {
            background: #f8fafc;
            border-top: 2px dashed #e5e7eb;
            padding: 1rem 1.5rem 1.75rem;
            text-align: center;
        }
        .receipt-footer .thank-you { font-size: 1rem; font-weight: 800; color: #1e1b4b; }
        .receipt-footer .footer-note { font-size: 0.75rem; color: #9ca3af; margin-top: 0.4rem; line-height: 1.5; }
        .receipt-footer .sale-no { font-size: 0.7rem; color: #c7d2fe; font-weight: 700; margin-top: 0.75rem; font-family: monospace; }

        /* ── Print Styles ── */
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                max-width: 80mm; /* Thermal paper width */
            }
            .btn { display: none; }
            .fe-pending-btn { display: none; }
        }
    </style>
</head>
<body>

    <!-- Action Buttons (not printed) -->
    <div class="action-bar">
        <a href="{{ route('pos.index') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
            Volver al POS
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/></svg>
            Imprimir
        </button>
    </div>

    <!-- Receipt -->
    <div class="receipt">

        <!-- Header -->
        <div class="receipt-header">
            <div class="logo-text">{{ $settings->get('business_name', config('app.name')) }}</div>
            @if($settings->get('business_address'))
                <div class="subtitle">{{ $settings->get('business_address') }}</div>
            @endif
            @if($settings->get('business_phone'))
                <div class="subtitle">Tel: {{ $settings->get('business_phone') }}</div>
            @endif
            <span class="receipt-type-badge {{ $sale->type === 'sale' ? 'badge-sale' : ($sale->type === 'waste' ? 'badge-waste' : 'badge-consumo') }}">
                {{ $sale->type === 'sale' ? '✓ Comprobante de Venta' : ($sale->type === 'waste' ? '⚠ Registro de Novedad' : '📋 Consumo Interno') }}
            </span>
        </div>

        <!-- Meta Info -->
        <div class="receipt-meta">
            <div class="meta-grid">
                <div class="meta-item">
                    <div class="label">No. Comprobante</div>
                    <div class="value">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Fecha</div>
                    <div class="value">{{ $sale->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Hora</div>
                    <div class="value">{{ $sale->created_at->format('h:i A') }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Caja / Sucursal</div>
                    <div class="value">{{ optional(optional($sale->session)->cashRegister)->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Cajero</div>
                    <div class="value">{{ optional($sale->user)->name ?? '—' }}</div>
                </div>
                <div class="meta-item">
                    <div class="label">Cliente</div>
                    <div class="value">
                        @if($sale->customer)
                            {{ $sale->customer->name }}
                        @else
                            Consumidor Final
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr class="dashed">

        <!-- Items -->
        <div class="receipt-items">
            <div class="item-header">
                <span>Descripción</span>
                <span style="text-align:center">Cant.</span>
                <span style="text-align:right">Total</span>
            </div>
            @foreach($sale->details as $detail)
                <div class="item-row">
                    <div>
                        <div class="item-name">{{ $detail->product->name ?? 'Producto eliminado' }}</div>
                        <div style="font-size:0.72rem;color:#9ca3af;">@ ${{ number_format($detail->price, 2) }} c/u</div>
                    </div>
                    <div class="item-qty">{{ $detail->quantity }}</div>
                    <div class="item-total">${{ number_format($detail->price * $detail->quantity, 2) }}</div>
                </div>
            @endforeach
        </div>

        <hr class="dashed">

        <!-- Totals -->
        <div class="receipt-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>${{ number_format($sale->total, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Descuentos</span>
                <span>$0.00</span>
            </div>
            <div class="total-row grand">
                <span>TOTAL</span>
                <span class="amount">${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        @if($sale->type === 'sale' && $sale->payments->count() > 0)
            <hr class="dashed">
            <!-- Payments Breakdown -->
            <div class="receipt-payments">
                <div style="font-size:0.68rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.6rem;">Pagos recibidos</div>
                @php $totalPaid = $sale->payments->sum('amount'); @endphp
                @foreach($sale->payments as $payment)
                    <div class="pay-row">
                        <span>{{ optional($payment->paymentMethod)->name ?? 'Pago' }}</span>
                        <span>${{ number_format($payment->amount, 2) }}</span>
                    </div>
                @endforeach
                @if($totalPaid > $sale->total)
                    <div style="text-align:right;font-size:0.82rem;font-weight:800;color:#059669;margin-top:0.5rem;">
                        Vuelto: ${{ number_format($totalPaid - $sale->total, 2) }}
                    </div>
                @endif
            </div>
        @endif

        <hr class="dashed">

        <!-- Electronic Invoice Section -->
        <div class="fe-section" id="fe-section">
            <div class="fe-title">Facturación Electrónica</div>
            @if($sale->is_electronic_invoiced)
                <div class="fe-issued">
                    <div class="fe-no">FE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="cufe-label">CUFE / Código Único:</div>
                    <div class="cufe-value" id="cufe-display">Factura emitida electrónicamente ✓</div>
                </div>
            @else
                <div style="font-size:0.78rem;color:#6366f1;font-weight:600;margin-bottom:0.75rem;">
                    Esta venta no ha sido facturada electrónicamente.
                </div>
                <button id="fe-btn" class="fe-pending-btn" onclick="emitirFacturaElectronica({{ $sale->id }})">
                    📄 Emitir Factura Electrónica (Mock)
                </button>
            @endif
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="thank-you">{{ $sale->type === 'consumo' ? 'Consumo Interno Registrado' : '¡Gracias por su compra!' }}</div>
            @if($settings->get('receipt_footer_note'))
                <div class="footer-note">{{ $settings->get('receipt_footer_note') }}</div>
            @else
                <div class="footer-note">Conserve este comprobante como prueba de su transacción.</div>
            @endif
            <div class="sale-no">REF: POS-{{ str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}</div>
        </div>

    </div>

    <script>
        async function emitirFacturaElectronica(saleId) {
            const btn = document.getElementById('fe-btn');
            btn.disabled = true;
            btn.textContent = 'Procesando... ⏳';

            try {
                const response = await fetch(`/sales/${saleId}/invoice-mock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const section = document.getElementById('fe-section');
                    section.innerHTML = `
                        <div class="fe-title">✅ Factura Electrónica Emitida</div>
                        <div class="fe-issued">
                            <div class="fe-no">${data.invoice_no}</div>
                            <div class="cufe-label">CUFE / Código Único:</div>
                            <div class="cufe-value">${data.cufe}</div>
                        </div>
                    `;
                } else {
                    btn.disabled = false;
                    btn.textContent = '📄 Emitir Factura Electrónica (Mock)';
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                btn.disabled = false;
                btn.textContent = '📄 Emitir Factura Electrónica (Mock)';
                alert('Error de red.');
                console.error(e);
            }
        }
    </script>
</body>
</html>
