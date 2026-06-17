<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1f2937; line-height: 1.6; background-color: #f4f4f7; padding: 40px 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="background-color: #4f46e5; color: white; padding: 40px; text-align: center;">
            <h1 style="margin:0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;">Cierre de Caja</h1>
            <p style="margin:8px 0 0 0; opacity: 0.9; font-size: 16px; font-weight: 500;">{{ $session->cashRegister->branch->name ?? 'Sede Principal' }}</p>
        </div>
        
        <div style="padding: 40px;">
            <p style="font-size: 16px; margin-bottom: 24px;">Se ha procesado un reporte de arqueo para la terminal <strong>{{ $session->cashRegister->name }}</strong>.</p>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; color: #6b7280; font-size: 14px;">Operador Responsable</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-align: right; font-weight: 700;">{{ $session->user->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; color: #6b7280; font-size: 14px;">Ventas en Efectivo</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-align: right; font-weight: 700;">$ {{ number_format($totalSales, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; color: #6b7280; font-size: 14px;">Efectivo Entregado</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-align: right; font-weight: 800; color: #4f46e5;">$ {{ number_format($reportedCash, 2) }}</td>
                </tr>
            </table>

            <div style="background-color: {{ $difference == 0 ? '#f0fdf4' : '#fef2f2' }}; padding: 30px; border-radius: 12px; text-align: center; border: 1px solid {{ $difference == 0 ? '#dcfce7' : '#fee2e2' }};">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Diferencia de Arqueo</div>
                <div style="font-size: 36px; font-weight: 800; color: {{ $difference == 0 ? '#166534' : '#991b1b' }}; letter-spacing: -0.05em;">
                    $ {{ number_format($difference, 2) }}
                </div>
                <div style="margin-top: 12px;">
                    @if($difference == 0)
                        <span style="background-color: #22c55e; color: white; padding: 4px 16px; border-radius: 9999px; font-size: 12px; font-weight: 800; text-transform: uppercase;">Caja Cuadrada</span>
                    @else
                        <span style="background-color: #ef4444; color: white; padding: 4px 16px; border-radius: 9999px; font-size: 12px; font-weight: 800; text-transform: uppercase;">Descuadre detectado</span>
                    @endif
                </div>
            </div>

            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #f3f4f6;">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">Apertura: {{ $session->opened_at->format('d/m/Y h:i A') }}</p>
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">Cierre: {{ $session->closed_at->format('d/m/Y h:i A') }}</p>
                        </td>
                        <td style="text-align: right;">
                             <p style="font-size: 12px; color: #9ca3af; margin: 0;">Base Inicial: $ {{ number_format($session->initial_balance, 2) }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div style="text-align: center; padding-bottom: 40px;">
            <p style="font-size: 12px; color: #9ca3af;">{{ config('app.name') }} POS System &bull; Control Administrativo</p>
        </div>
    </div>
</body>
</html>
