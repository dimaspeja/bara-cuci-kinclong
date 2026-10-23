<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @php
        $columns = $this->columns();
        $rows = $this->rows();
    @endphp

    <x-filament::section>
        @if (count($rows) === 0)
            <p style="opacity: .7">Tidak ada data untuk filter ini.</p>
        @else
            <div style="overflow-x: auto">
                <table style="width: 100%; border-collapse: collapse; font-size: .875rem">
                    <thead>
                        <tr>
                            @foreach ($columns as $label)
                                <th style="text-align: left; padding: .5rem .75rem; white-space: nowrap; border-bottom: 1px solid rgba(128, 128, 128, .35)">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach (array_keys($columns) as $key)
                                    <td style="padding: .5rem .75rem; border-bottom: 1px solid rgba(128, 128, 128, .15)">
                                        {{ $row[$key] ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
