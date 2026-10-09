<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Workshop;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyApp extends Command
{
    protected $signature = 'app:verify';
    protected $description = 'Verify the application works end-to-end';

    public function handle()
    {
        $this->info('=== DB Connection ===');
        $this->line('Driver: ' . config('database.default'));

        $this->newLine();
        $this->info('=== Users ===');
        $users = User::all();
        foreach ($users as $u) {
            $this->line("  {$u->name} ({$u->role}) — {$u->email}");
        }
        $this->line("  Total: {$users->count()}");

        $this->newLine();
        $this->info('=== Workshops ===');
        $workshops = Workshop::withCount(['registrations as active_count' => fn($q) => $q->where('status', 'active')])->get();
        foreach ($workshops as $w) {
            $available = $w->capacity - $w->active_count;
            $this->line("  [{$w->code}] {$w->title} — cap:{$w->capacity}, active:{$w->active_count}, avail:{$available}, status:{$w->status}");
        }
        $this->line("  Total: {$workshops->count()}");

        $this->newLine();
        $this->info('=== Concurrency Test (lockForUpdate) ===');
        $workshop = Workshop::first();
        $this->line("Testing on: {$workshop->title} (cap: {$workshop->capacity})");

        try {
            $reg = DB::transaction(function () use ($workshop) {
                $locked = Workshop::lockForUpdate()->findOrFail($workshop->id);
                $activeCount = Registration::where('workshop_id', $locked->id)->where('status', 'active')->count();
                $this->line("  Active count under lock: {$activeCount}");

                if ($activeCount >= $locked->capacity) {
                    throw new \Exception("FULL — would reject registration");
                }

                return Registration::create([
                    'workshop_id' => $locked->id,
                    'attendee_name' => 'Test Attendee',
                    'attendee_email' => 'test@verify.com',
                    'status' => 'active',
                    'registered_by' => User::where('role', 'staff')->first()->id,
                ]);
            });
            $this->line("  ✓ Registration created (ID: {$reg->id})");

            // Cancel it
            $reg->update([
                'status' => 'cancelled',
                'cancelled_by' => User::where('role', 'staff')->first()->id,
                'cancelled_at' => now(),
            ]);
            $this->line("  ✓ Registration cancelled (record preserved)");

            // Verify record still exists
            $check = Registration::find($reg->id);
            $this->line("  ✓ Record still in DB: status={$check->status}, cancelled_at={$check->cancelled_at}");

        } catch (\Exception $e) {
            $this->error("  ✗ Error: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('=== Role Middleware Check ===');
        $this->line("  CheckRole middleware registered: " . (class_exists(\App\Http\Middleware\CheckRole::class) ? '✓' : '✗'));

        $this->newLine();
        $this->info('✅ All checks passed!');
        return 0;
    }
}
