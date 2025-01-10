<div class="mt-6 space-y-6">
    @if ($clientsProducts->count() > 0)
        @foreach ($clientsProducts as $client)
            <div class="my-4 px-6 py-4 bg-white rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition duration-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                <div class="p-4 leading-normal">
                    <div class="mb-2">
                        <span class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $client->company ? $client->company : $client->first_name . ' ' . $client->last_name }}
                        </span>
                    </div>
                    <div class="flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div>
                            <strong class="text-sm">Phone:</strong>
                            <p class="text-xs">{{ $client->phone }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">Email:</strong>
                            <p class="text-xs">{{ $client->email }}</p>
                        </div>
                        <div>
                            <strong class="text-sm">{{ $client->company ? 'Representative:':'Company:' }}</strong>
                            <p class="text-xs">{{ $client->company ? $client->first_name . ' ' . $client->last_name : $client->company }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex space-x-6 text-gray-600 dark:text-gray-400">
                        <div><strong class="text-sm">Address:</strong>
                            <p class="text-xs">{{ $client->address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer's Devices Section -->
                @if ($client->clientProducts->count() > 0)
                <div class="p-4 leading-normal mt-2">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Devices</h3>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border-collapse border border-gray-300 dark:border-gray-700 text-sm">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-800 text-left">
                                    <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Product</th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Serial</th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">IMEI</th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-4 py-2">Color</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($client->clientProducts as $clientProduct)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer relative"
                                    title="{{ $clientProduct->product->description }}"
                                    onclick="window.location.href='#';">
                                    <!-- Product Column (Combined Manufacturer, Brand, Model Name) -->
                                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                        {{ $clientProduct->product->manufacturer }}
                                        {{ $clientProduct->product->brand }}
                                        {{ $clientProduct->product->model_name }}
                                    </td>
                                    <!-- Serial Column -->
                                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">{{ $clientProduct->serial }}</td>
                                    <!-- IMEI Column -->
                                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">{{ $clientProduct->imei }}</td>
                                    <!-- Color Column -->
                                    <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">{{ $clientProduct->color }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>


                @else
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No devices listed for this client.
                    </div>
                @endif
            </div>
        @endforeach
        {{ $clientsProducts->links() }}
    @else
        <div class="text-center text-gray-600 dark:text-gray-400 mt-4">
            No clientsProducts found.
        </div>
    @endif
</div>
