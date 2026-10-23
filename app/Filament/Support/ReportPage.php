<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Dasar untuk halaman laporan: formulir filter di atas, tabel hasil di bawah,
 * dan tombol ekspor CSV. Hanya Admin yang bisa membuka.
 */
abstract class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.report';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    /** Nilai filter (di-bind ke formulir). */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill($this->defaultFilters());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->filterSchema())
            ->columns(4)
            ->statePath('data');
    }

    /** @return array<string, mixed> */
    protected function defaultFilters(): array
    {
        return [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ];
    }

    /** @return array<int, mixed> */
    abstract protected function filterSchema(): array;

    /** @return array<string, string> kunci => judul kolom */
    abstract public function columns(): array;

    /** @return array<int, array<string, mixed>> */
    abstract public function rows(): array;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Ekspor CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $columns = $this->columns();
        $rows = $this->rows();
        $filename = Str::slug((string) $this->getTitle()).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, array_values($columns), ',', '"', '\\');

            foreach ($rows as $row) {
                fputcsv(
                    $out,
                    array_map(fn (string $key) => $row[$key] ?? '', array_keys($columns)),
                    ',', '"', '\\'
                );
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
