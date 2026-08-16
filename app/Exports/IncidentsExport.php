<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\BorderStyle;

class IncidentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Incident::with(['user', 'assignedTo', 'mediaEvidence'])
                        ->latest();

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('incident_type', $this->filters['type']);
        }

        if (!empty($this->filters['priority'])) {
            $query->where('priority', $this->filters['priority']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Reference No.',
            'Report ID',
            'Reporter Name',
            'Reporter Email',
            'Incident Type',
            'Description',
            'Incident Date',
            'Incident Time',
            'Priority',
            'Status',
            'Coordinates',
            'Media Count',
            'Assigned To',
            'Date Reported',
            'Last Updated'
        ];
    }

    public function map($incident): array
    {
        // incidents.user_id is ON DELETE SET NULL, so the reporter may legitimately be
        // gone. Reading ->user->fname unguarded meant deleting a single citizen broke
        // every export from then on.
        $reporter = $incident->user;

        return [
            $incident->reference_number,
            $incident->id,
            $reporter?->name ?? 'Deleted account',
            $reporter?->email ?? '—',
            $incident->incident_type,
            strip_tags($incident->description),
            $incident->incident_date?->format('Y-m-d'),
            $incident->incident_time,
            $incident->priority,
            $incident->status,
            $this->coordinatesFor($incident),
            $incident->mediaEvidence->count(),
            $incident->assignedTo?->name ?? 'Not Assigned',
            $incident->created_at->format('Y-m-d H:i:s'),
            $incident->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Incidents have no `location` column — the previous value was always null. The
     * nearest real data is the coordinates recorded against the media evidence.
     */
    private function coordinatesFor($incident): string
    {
        $located = $incident->mediaEvidence->first(fn ($media) => $media->hasLocation());

        return $located ? "{$located->latitude}, {$located->longitude}" : 'N/A';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],

            // Set auto-size for columns
            'A:O' => [
                'alignment' => [
                    'wrapText' => true
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Incident Reports';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get the last row with data
                $highestRow = $sheet->getHighestRow();

                // Add empty rows
                $signatoryStartRow = $highestRow + 3;

                // Add "SIGNATORIES" header
                // $sheet->setCellValue('A' . $signatoryStartRow, 'SIGNATORIES');
                $sheet->getStyle('A' . $signatoryStartRow)->getFont()->setBold(true);

                // Add signatory line
                $signatureRow = $signatoryStartRow + 2;
                $sheet->setCellValue('A' . $signatureRow, '________________________________');
                $sheet->setCellValue('A' . ($signatureRow + 1), 'DENR CENRO Officer');
                $sheet->getStyle('A' . ($signatureRow + 1))->getFont()->setBold(true);
                $sheet->setCellValue('A' . ($signatureRow + 2), 'Signature over Printed Name');

                // Add date field
                $sheet->setCellValue('A' . ($signatureRow + 3), 'Date: _______________');

                // Set column width
                $sheet->getColumnDimension('A')->setWidth(35);
            },
        ];
    }
}
