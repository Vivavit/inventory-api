@props([
    'headers' => [],
    'class' => ''
])

@php
    $tableClasses = 'data-table';
    $tableClasses .= ' ' . $class;
@endphp

<div class="table-shell">
    <div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if($headers)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
    </div>
</div>
