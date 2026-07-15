<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendCashRegisterClosedMail;
use App\Mail\CashRegisterClosedMail;
use App\Models\CashRegisterSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendCashRegisterClosedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_mail_to_all_admins(): void
    {
        Mail::fake();

        $adminRole = Role::factory()->admin()->create();
        $salesRole = Role::factory()->sales()->create();

        User::factory()->count(3)->create(['role_id' => $adminRole->id]);
        User::factory()->count(2)->create(['role_id' => $salesRole->id]);

        $session = CashRegisterSession::factory()->closed()->create();

        $job = new SendCashRegisterClosedMail($session, 500.00, 600.00);
        $job->handle();

        Mail::assertQueued(CashRegisterClosedMail::class, 3);
    }

    public function test_job_does_not_fail_when_no_admins(): void
    {
        Mail::fake();

        $session = CashRegisterSession::factory()->closed()->create();

        $job = new SendCashRegisterClosedMail($session, 500.00, 600.00);

        try {
            $job->handle();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('El Job no debe fallar cuando no hay administradores: ' . $e->getMessage());
        }

        Mail::assertNothingSent();
    }

    public function test_job_continues_when_individual_mail_fails(): void
    {
        Mail::fake();

        $adminRole = Role::factory()->admin()->create();
        $admin1 = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin1@test.com']);
        $admin2 = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin2@test.com']);

        $session = CashRegisterSession::factory()->closed()->create();

        Mail::shouldReceive('to')
            ->with($admin1->email)
            ->andThrow(new \RuntimeException('SMTP error'));

        $job = new SendCashRegisterClosedMail($session, 500.00, 600.00);

        try {
            $job->handle();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('El Job debe continuar aunque un envío falle');
        }
    }
}
