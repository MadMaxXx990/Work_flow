<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->data),
            new EmployeePerformanceSheet($this->data),
        ];
    }
}

// ── Sheet 1: Summary ─────────────────────────────────────────────────────────
class SummarySheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Summary'; }

    public function array(): array
    {
        $d = $this->data;
        return [
            ['Flowline — Performance Report'],
            ['Period', $d['dateFrom'] . ' to ' . $d['dateTo']],
            ['Generated', now()->format('M j, Y g:i a')],
            [],
            ['Metric', 'Value'],
            ['Total Tasks',      $d['totalTasks']],
            ['Completed Tasks',  $d['completedTasks']],
            ['Overdue Tasks',    $d['overdueTasks']],
            ['Completion Rate',  $d['completionRate'] . '%'],
            ['Present Today',    $d['attendanceSummary']['Present'] ?? 0],
            [],
            ['Tasks by Stage'],
            ['Stage', 'Count'],
            ...collect($d['stages'])->map(fn($s, $i) => [$s->stage_name, $d['tasksByStage'][$i] ?? 0])->toArray(),
            [],
            ['Tasks by Priority'],
            ['Priority', 'Count'],
            ...collect($d['priorities'])->map(fn($p, $i) => [$p->priority_name, $d['tasksByPriority'][$i] ?? 0])->toArray(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true]],
        ];
    }
}

// ── Sheet 2: Employee Performance ─────────────────────────────────────────────
class EmployeePerformanceSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Employee Performance'; }

    public function headings(): array
    {
        return ['Employee', 'Department', 'Position', 'Assigned', 'Completed', 'Overdue', 'Completion Rate (%)'];
    }

    public function array(): array
    {
        return collect($this->data['employeePerformance'])->map(fn($row) => [
            $row['employee']->full_name,
            $row['employee']->department?->department_name ?? '—',
            $row['employee']->position?->position_name ?? '—',
            $row['assigned'],
            $row['completed'],
            $row['overdue'],
            $row['completion_rate'],
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFEEF2FF']]],
        ];
    }
}
