<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\CashRegisterClosedMail;
use App\Models\CashRegisterSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCashRegisterClosedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CashRegisterSession $session;

    public float $totalSales;

    public float $finalReportedBalance;

    /**
     * Create a new job instance.
     */
    public function __construct(CashRegisterSession $session, float $totalSales, float $finalReportedBalance)
    {
        $this->session = $session;
        $this->totalSales = $totalSales;
        $this->finalReportedBalance = $finalReportedBalance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $admins = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->get();

        if ($admins->isEmpty()) {
            Log::info('No hay administradores registrados para notificar el cierre de caja.');

            return;
        }

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(
                    new CashRegisterClosedMail($this->session, $this->totalSales, $this->finalReportedBalance)
                );
            } catch (\Exception $e) {
                Log::error("Error enviando correo de cierre de caja a {$admin->email}: ".$e->getMessage());
            }
        }
    }
}
