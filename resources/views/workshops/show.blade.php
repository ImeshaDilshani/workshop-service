<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $workshop->title }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ $workshop->code }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('workshops.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">← Back to list</a>
                @if(auth()->user()->isManager())
                    <a href="{{ route('workshops.edit', $workshop) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                        Edit
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Workshop Details --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Workshop Details</h3>

                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $workshop->instructor }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $workshop->start_date->format('l, M j, Y \a\t g:ia') }}</dd>
                                </div>
                                @if($workshop->location)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Location</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $workshop->location }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</dt>
                                    <dd class="mt-1">
                                        @php
                                            $statusColors = [
                                                'scheduled' => 'bg-blue-100 text-blue-800',
                                                'ongoing' => 'bg-green-100 text-green-800',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$workshop->status] }}">
                                            {{ ucfirst($workshop->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</dt>
                                    <dd class="mt-1">
                                        @php $available = $workshop->capacity - $workshop->active_registrations_count; @endphp
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-2xl font-bold {{ $available <= 0 ? 'text-red-600' : ($available <= 3 ? 'text-amber-600' : 'text-green-600') }}">{{ $available }}</span>
                                            <span class="text-sm text-gray-500">of {{ $workshop->capacity }} seats available</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="h-2.5 rounded-full {{ $available <= 0 ? 'bg-red-500' : ($available <= 3 ? 'bg-amber-500' : 'bg-green-500') }}"
                                                 style="width: {{ min(100, ($workshop->active_registrations_count / max(1, $workshop->capacity)) * 100) }}%"></div>
                                        </div>
                                    </dd>
                                </div>
                                @if($workshop->description)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-700 leading-relaxed">{{ $workshop->description }}</dd>
                                </div>
                                @endif
                            </dl>

                            {{-- Register Button --}}
                            @if($workshop->status === 'scheduled' || $workshop->status === 'ongoing')
                                @if($available > 0)
                                    <div class="mt-6 pt-6 border-t border-gray-100">
                                        <a href="{{ route('registrations.create', $workshop) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                                            Register Attendee
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-6 pt-6 border-t border-gray-100">
                                        <div class="text-center py-3 bg-red-50 rounded-lg">
                                            <p class="text-sm font-medium text-red-700">Workshop Full — No Seats Available</p>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Registrations --}}
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Registrations
                                    <span class="text-sm font-normal text-gray-500">({{ $registrations->where('status', 'active')->count() }} active)</span>
                                </h3>
                                <a href="{{ route('registrations.history', $workshop) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                    Full History →
                                </a>
                            </div>

                            @if($registrations->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered By</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($registrations as $registration)
                                                <tr class="{{ $registration->status === 'cancelled' ? 'bg-gray-50 opacity-60' : '' }} hover:bg-gray-50 transition-colors duration-100">
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <span class="text-sm font-medium text-gray-900">{{ $registration->attendee_name }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $registration->attendee_email }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($registration->status === 'active')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                                        {{ $registration->registeredByUser->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $registration->created_at->format('M j, g:ia') }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                                        @if($registration->status === 'active')
                                                            <form method="POST" action="{{ route('registrations.cancel', $registration) }}" class="inline" onsubmit="return confirm('Cancel registration for {{ $registration->attendee_name }}? This will free up their seat.')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                                    Cancel
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-xs text-gray-400">
                                                                by {{ $registration->cancelledByUser->name ?? 'N/A' }}
                                                                {{ $registration->cancelled_at ? $registration->cancelled_at->format('M j') : '' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                    <p class="text-sm text-gray-500">No registrations yet.</p>
                                    @if(in_array($workshop->status, ['scheduled', 'ongoing']))
                                        <a href="{{ route('registrations.create', $workshop) }}" class="mt-2 inline-flex text-sm text-indigo-600 hover:text-indigo-800 font-medium">Register the first attendee →</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
