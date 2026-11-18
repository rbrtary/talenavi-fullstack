<?php

namespace App\Exports;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TodosExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return Todo::query()
            ->filter($this->filters)
            ->orderBy('due_date')
            ->orderBy('id');
    }

    public function title(): string
    {
        return 'Todo Report';
    }

    public function headings(): array
    {
        return ['title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority'];
    }

    /**
     * @param  Todo  $todo
     */
    public function map(mixed $todo): array
    {
        return [
            $todo->title,
            $todo->assignee,
            ExcelDate::dateTimeToExcel($todo->due_date),
            $todo->time_tracked,
            $todo->status->value,
            $todo->priority->value,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'D' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFCC3606']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->addSummaryRow($event->sheet->getDelegate());
            },
        ];
    }

    private function addSummaryRow(Worksheet $sheet): void
    {
        $row = $sheet->getHighestDataRow() + 1;
        $todos = Todo::query()->filter($this->filters);

        $sheet->setCellValue("A{$row}", 'Total Todos');
        $sheet->setCellValue("B{$row}", $todos->count());
        $sheet->setCellValue("C{$row}", 'Total Time Tracked');
        $sheet->setCellValue("D{$row}", round((float) $todos->sum('time_tracked'), 2));

        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    }
}