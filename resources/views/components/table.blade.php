@props([
    'headers' => [],
    'class' => ''
])

@php
    $tableClasses = 'w-full bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm';
    $tableClasses .= ' ' . $class;
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if($headers)
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>