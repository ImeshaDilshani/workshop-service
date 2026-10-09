<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('audit.index') }}" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                            <select name="action" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Actions</option>
                                @foreach(['created_user', 'updated_user', 'deleted_user', 'created_workshop', 'updated_workshop', 'registered_attendee', 'cancelled_registration'] as $action)
                                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                Filter
                            </button>
                            <a href="{{ route('audit.index') }}" class="ml-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->created_at->format('M j, Y g:ia') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $log->user->name ?? 'Deleted User' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $actionColors = [
                                                'created_user' => 'bg-green-100 text-green-800',
                                                'updated_user' => 'bg-blue-100 text-blue-800',
                                                'deleted_user' => 'bg-red-100 text-red-800',
                                                'created_workshop' => 'bg-green-100 text-green-800',
                                                'updated_workshop' => 'bg-blue-100 text-blue-800',
                                                'registered_attendee' => 'bg-emerald-100 text-emerald-800',
                                                'cancelled_registration' => 'bg-amber-100 text-amber-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($log->new_values)
                                            <details class="cursor-pointer">
                                                <summary class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">View details</summary>
                                                <div class="mt-2 text-xs bg-gray-50 rounded p-2 max-w-md">
                                                    @if($log->old_values)
                                                        <p class="font-medium text-gray-700 mb-1">Old:</p>
                                                        <pre class="whitespace-pre-wrap text-gray-500">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                    @endif
                                                    <p class="font-medium text-gray-700 mb-1 {{ $log->old_values ? 'mt-2' : '' }}">New:</p>
                                                    <pre class="whitespace-pre-wrap text-gray-500">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </details>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">No audit log entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
