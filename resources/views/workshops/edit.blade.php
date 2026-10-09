<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Workshop') }}: {{ $workshop->title }}
            </h2>
            <a href="{{ route('workshops.show', $workshop) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">← Back to workshop</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <form method="POST" action="{{ route('workshops.update', $workshop) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="code" :value="__('Workshop Code')" />
                                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $workshop->code)" required />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $workshop->title)" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="instructor" :value="__('Instructor')" />
                                <x-text-input id="instructor" name="instructor" type="text" class="mt-1 block w-full" :value="old('instructor', $workshop->instructor)" required />
                                <x-input-error :messages="$errors->get('instructor')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="start_date" :value="__('Date & Time')" />
                                <x-text-input id="start_date" name="start_date" type="datetime-local" class="mt-1 block w-full" :value="old('start_date', $workshop->start_date->format('Y-m-d\TH:i'))" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="capacity" :value="__('Capacity (seats)')" />
                                <x-text-input id="capacity" name="capacity" type="number" class="mt-1 block w-full" :value="old('capacity', $workshop->capacity)" required min="1" max="999" />
                                <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach(['scheduled', 'ongoing', 'completed', 'cancelled'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $workshop->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="location" :value="__('Location (optional)')" />
                                <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $workshop->location)" />
                                <x-input-error :messages="$errors->get('location')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-input-label for="description" :value="__('Description (optional)')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $workshop->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-100">
                            <a href="{{ route('workshops.show', $workshop) }}" class="text-sm text-gray-600 hover:text-gray-800 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Workshop') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
