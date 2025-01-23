<div class="mt-6">
    @if ($boardIds->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-700">
                <thead>
                    <tr
                        class="bg-gray-100 dark:bg-gray-800 text-xs font-semibold uppercase text-gray-700 dark:text-gray-300">

                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Model</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Build Name</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Mass-Prod. HWID</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">Pre-Prod. HWID</th>
                        <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($boardIds as $boardId)
                        <tr
                            class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->product->manufacturer }}
                                {{ $boardId->product->brand }}
                                {{ $boardId->product->model_name }}
                                {{ $boardId->product->model_number }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->build_name }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->mass_production_hwid }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                {{ $boardId->pre_production_hwid }}
                            </td>
                            <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-center">
                                <div class="flex justify-center space-x-4">
                                    <button wire:click="$dispatch('edit', {id: {{ $boardId->id }}})"
                                        class="flex py-1 px-3 bg-orange-500 hover:bg-red-600 text-sm text-white rounded-md">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $boardId->id }})"
                                        wire:confirm="Are you sure you want to delete this device?"
                                        class="flex py-1 px-3 bg-green-500 hover:bg-green-400 text-sm text-white rounded-md">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr
                            class="text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td colspan="8" class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-left">
                                <p><strong>Repair Methods:</strong>
                                    {{-- {{ implode(', ', $boardId->repair_methods ?? []) }} --}}
                                    @foreach ($boardId->repair_methods as $method)
                                        @php
                                            $class = App\Enums\RepairMethod::from($method)->color();
                                        @endphp
                                        <span
                                            class="text-xs font-medium me-2 px-2.5 py-0.5 rounded {{ $class }}">
                                            {{ ucfirst($method) }}
                                        </span>
                                    @endforeach
                                </p>
                                <p><strong>Resistances:</strong>
                                    @if (!empty($boardId->resistances))
                                        @php
                                            // Gelen JSON string veriyi diziye çeviriyoruz
                                            //$resistances = json_decode($boardId->resistances, true);
                                            $resistances = $boardId->resistances;
                                            $resistanceCount = count($resistances);
                                            $index = 0;
                                        @endphp
                                        @foreach ($resistances as $key => $value)
                                            @php
                                                // Direnci SMD koduna dönüştür
                                                $smdCode = convertToSMDCode($value);
                                                $isFirstInPair = $index % 2 === 0; // Her iki dirençte bir yeni grup başlat
                                                $index++;
                                            @endphp
                                            @if ($isFirstInPair)
                                                <div style="display: flex; align-items: center;">
                                                    <span>V_in=1.8V >---</span>
                                            @endif
                                            <svg xmlns="http://www.w3.org/2000/svg" width="46" height="24" version="1.1">
                                                <title>{{ $key }}: {{ $value }}Ω</title>
                                                <g>
                                                    <rect style="fill:#aaa;fill-opacity:1;fill-rule:nonzero;stroke:#000;stroke-width:1;stroke-opacity:1"
                                                        width="43" height="21" y="1.5" x="1.5" />
                                                    <rect style="fill:#000;fill-opacity:1;fill-rule:nonzero;stroke:none" width="26" height="22"
                                                        x="10" y="1" />
                                                    <text
                                                        style="font-size:14px;text-align:center;text-anchor:middle;fill:#fff;fill-opacity:1;font-family:monospace"
                                                        x="22.750488" y="17.195312">
                                                        {{ $smdCode }}
                                                    </text>
                                                </g>
                                            </svg>
                                            @if ($isFirstInPair)
                                                    <span>---+V_out+---</span>
                                            @endif

                                            @if (!$isFirstInPair || $index === $resistanceCount)
                                                @if ($index % 2 === 0)
                                                    <span>---> GND</span>
                                                @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <p>N/A</p>
                                    @endif
                                </p>
                                <p><strong>Description:</strong> {{ $boardId->description ?? 'N/A' }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $boardIds->links() }}
        </div>
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No boardIds found.
        </div>
    @endif
</div>
