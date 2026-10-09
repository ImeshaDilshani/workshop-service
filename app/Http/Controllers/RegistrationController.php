<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Workshop;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    /**
     * Show the registration form for a workshop.
     */
    public function create(Workshop $workshop)
    {
        $workshop->loadCount(['registrations as active_registrations_count' => fn($q) => $q->where('status', 'active')]);

        return view('registrations.create', compact('workshop'));
    }

    /**
     * Store a new registration.
     *
     * Uses pessimistic locking (SELECT ... FOR UPDATE) to prevent
     * two staff members from booking the last seat simultaneously.
     * The workshop row is locked for the duration of the transaction,
     * so only one concurrent request can read and update at a time.
     */
    public function store(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $registration = DB::transaction(function () use ($workshop, $validated) {
                // Pessimistic lock: lock the workshop row to prevent concurrent overbooking.
                // Any other transaction trying to register for the same workshop will
                // block here until this transaction commits or rolls back.
                $lockedWorkshop = Workshop::lockForUpdate()->findOrFail($workshop->id);

                // Count active registrations while holding the lock
                $activeCount = Registration::where('workshop_id', $lockedWorkshop->id)
                    ->where('status', 'active')
                    ->count();

                if ($activeCount >= $lockedWorkshop->capacity) {
                    throw new \Exception('This workshop is full. No seats available.');
                }

                if ($lockedWorkshop->status === 'cancelled') {
                    throw new \Exception('Cannot register for a cancelled workshop.');
                }

                if ($lockedWorkshop->status === 'completed') {
                    throw new \Exception('Cannot register for a completed workshop.');
                }

                return Registration::create([
                    'workshop_id' => $lockedWorkshop->id,
                    'attendee_name' => $validated['attendee_name'],
                    'attendee_email' => $validated['attendee_email'],
                    'status' => 'active',
                    'registered_by' => auth()->id(),
                ]);
            });

            AuditLog::record(
                auth()->id(),
                'registered_attendee',
                Registration::class,
                $registration->id,
                null,
                [
                    'workshop_id' => $workshop->id,
                    'attendee_name' => $validated['attendee_name'],
                    'attendee_email' => $validated['attendee_email'],
                ]
            );

            return redirect()->route('workshops.show', $workshop)
                ->with('success', "Successfully registered {$validated['attendee_name']}.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a registration.
     *
     * The record is never deleted — status is flipped to 'cancelled'
     * and the cancelling user + timestamp are recorded.
     */
    public function cancel(Registration $registration)
    {
        if ($registration->status === 'cancelled') {
            return back()->with('error', 'This registration is already cancelled.');
        }

        $oldValues = [
            'status' => $registration->status,
        ];

        $registration->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
        ]);

        AuditLog::record(
            auth()->id(),
            'cancelled_registration',
            Registration::class,
            $registration->id,
            $oldValues,
            [
                'status' => 'cancelled',
                'workshop_id' => $registration->workshop_id,
                'attendee_name' => $registration->attendee_name,
            ]
        );

        return redirect()->route('workshops.show', $registration->workshop_id)
            ->with('success', "Registration for {$registration->attendee_name} has been cancelled. The seat is now available.");
    }

    /**
     * View full registration history for a workshop.
     */
    public function history(Workshop $workshop)
    {
        $registrations = $workshop->registrations()
            ->with(['registeredByUser', 'cancelledByUser'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('registrations.history', compact('workshop', 'registrations'));
    }
}
