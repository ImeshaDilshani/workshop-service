<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Register Attendee') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $workshop->title }} ({{ $workshop->code }})</p>
            </div>
            <a href="{{ route('workshops.show', $workshop) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">← Back to workshop</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Capacity Warning --}}
            @php $available = $workshop->capacity - $workshop->active_registrations_count; @endphp
            @if($available <= 3 && $available > 0)
                <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-medium text-amber-800">Only {{ $available }} seat{{ $available !== 1 ? 's' : '' }} remaining!</p>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <form method="POST" action="{{ route('registrations.store', $workshop) }}">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="attendee_name" :value="__('Attendee Name')" />
                                <x-text-input id="attendee_name" name="attendee_name" type="text" class="mt-1 block w-full" :value="old('attendee_name')" required autofocus placeholder="Full name of the attendee" />
                                <x-input-error :messages="$errors->get('attendee_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="attendee_email" :value="__('Attendee Email')" />
                                <x-text-input id="attendee_email" name="attendee_email" type="email" class="mt-1 block w-full" :value="old('attendee_email')" required placeholder="email@example.com" />
                                <x-input-error :messages="$errors->get('attendee_email')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                            <p class="text-xs text-gray-400">Registering as {{ auth()->user()->name }}</p>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('workshops.show', $workshop) }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                                <x-primary-button>
                                    {{ __('Register Attendee') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
