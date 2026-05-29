<x-filament-panels::page>
    @php($stages = $this->stages())

    <div class="flex gap-4 overflow-x-auto pb-4">
        @forelse ($stages as $stage)
            <div class="flex-shrink-0 w-72">
                <div class="flex items-center justify-between mb-3">
                    <x-filament::badge :color="$stage->color ?: 'gray'">
                        {{ $stage->name }}
                    </x-filament::badge>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $stage->applications->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($stage->applications as $application)
                        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <a
                                href="{{ \App\Filament\Resources\Applications\ApplicationResource::getUrl('edit', ['record' => $application]) }}"
                                class="font-medium text-gray-950 hover:underline dark:text-white"
                            >
                                {{ $application->name }}
                            </a>

                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $application->vacancy?->title }}
                            </p>

                            @if ($application->averageScore() !== null)
                                <div class="mt-2">
                                    <x-filament::badge size="sm" color="info">
                                        {{ $application->averageScore() }}/5
                                    </x-filament::badge>
                                </div>
                            @endif

                            <div class="mt-3 flex items-center justify-between">
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-left"
                                    size="sm"
                                    color="gray"
                                    label="Vorige fase"
                                    wire:click="moveBack({{ $application->id }})"
                                    :disabled="$loop->parent->first"
                                />
                                <x-filament::icon-button
                                    icon="heroicon-m-arrow-right"
                                    size="sm"
                                    color="gray"
                                    label="Volgende fase"
                                    wire:click="moveForward({{ $application->id }})"
                                    :disabled="$loop->parent->last"
                                />
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-200 p-3 text-center text-xs text-gray-400 dark:border-gray-700">
                            Geen sollicitaties
                        </p>
                    @endforelse
                </div>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400">
                Er zijn nog geen pipeline-fases ingesteld.
            </p>
        @endforelse
    </div>
</x-filament-panels::page>
