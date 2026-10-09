<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    /**
     * Display a listing of workshops with filtering.
     */
    public function index(Request $request)
    {
        $query = Workshop::withCount(['registrations as active_registrations_count' => function ($q) {
            $q->where('status', 'active');
        }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to . ' 23:59:59');
        }

        // Filter by available seats — uses a correlated subquery instead of HAVING
        // so it works correctly with MySQL strict mode
        if ($request->boolean('available_only')) {
            $query->whereRaw('capacity > (SELECT COUNT(*) FROM registrations WHERE registrations.workshop_id = workshops.id AND registrations.status = ?)', ['active']);
        }

        // Search by title or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('instructor', 'like', "%{$search}%");
            });
        }

        $workshops = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

        return view('workshops.index', compact('workshops'));
    }

    /**
     * Show the form for creating a new workshop.
     */
    public function create()
    {
        return view('workshops.create');
    }

    /**
     * Store a newly created workshop.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:workshops'],
            'title' => ['required', 'string', 'max:255'],
            'instructor' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        $workshop = Workshop::create($validated);

        AuditLog::record(
            auth()->id(),
            'created_workshop',
            Workshop::class,
            $workshop->id,
            null,
            $validated
        );

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop created successfully.');
    }

    /**
     * Display the specified workshop.
     */
    public function show(Workshop $workshop)
    {
        $workshop->loadCount(['registrations as active_registrations_count' => function ($q) {
            $q->where('status', 'active');
        }]);

        $registrations = $workshop->registrations()
            ->with(['registeredByUser', 'cancelledByUser'])
            ->orderByDesc('created_at')
            ->get();

        return view('workshops.show', compact('workshop', 'registrations'));
    }

    /**
     * Show the form for editing the specified workshop.
     */
    public function edit(Workshop $workshop)
    {
        return view('workshops.edit', compact('workshop'));
    }

    /**
     * Update the specified workshop.
     */
    public function update(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:workshops,code,' . $workshop->id],
            'title' => ['required', 'string', 'max:255'],
            'instructor' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        $oldValues = $workshop->only(['code', 'title', 'instructor', 'start_date', 'location', 'description', 'capacity', 'status']);

        $workshop->update($validated);

        AuditLog::record(
            auth()->id(),
            'updated_workshop',
            Workshop::class,
            $workshop->id,
            $oldValues,
            $validated
        );

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop updated successfully.');
    }
}
