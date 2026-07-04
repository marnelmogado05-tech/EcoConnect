<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Reports - Print</title>
    <style>
        @media print {
            @page {
                margin: 0.5in;
                size: landscape;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }

            .no-print {
                display: none !important;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
            }

            .table th,
            .table td {
                border: 1px solid #ddd;
                padding: 6px;
                text-align: left;
            }

            .table th {
                background-color: #f8f9fa;
                font-weight: bold;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #333;
                padding-bottom: 10px;
            }

            .footer {
                margin-top: 20px;
                text-align: center;
                font-size: 10px;
                color: #666;
            }

            .badge {
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }

            .text-success { color: #198754; }
            .text-warning { color: #ffc107; }
            .text-danger { color: #dc3545; }
            .text-info { color: #0dcaf0; }
            .text-secondary { color: #6c757d; }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="header">
        <h2>Incident Reports - DENR CENRO Sanchez Mira</h2>
        <p>Generated on: {{ $printDate }}</p>

        @if(!empty(array_filter($filters)))
        <div class="filters">
            <strong>Filters Applied:</strong>
            @foreach($filters as $key => $value)
                @if(!empty($value))
                    <span class="badge bg-secondary">{{ ucfirst($key) }}: {{ $value }}</span>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Ref No.</th>
                <th>Reporter</th>
                <th>Type</th>
                <th>Description</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Incident Date</th>
                <th>Reported Date</th>
                <th>Media</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $incident)
            <tr>
                <td>{{ $incident->reference_number }}</td>
                <td>{{ $incident->user->fname }} {{ $incident->user->lname }}</td>
                <td>{{ $incident->incident_type }}</td>
                <td>{{ \Illuminate\Support\Str::limit(strip_tags($incident->description), 50) }}</td>
                <td>
                    <span class="badge
                        @if($incident->priority == 'Urgent') bg-danger
                        @elseif($incident->priority == 'High') bg-warning
                        @else bg-success @endif">
                        {{ $incident->priority }}
                    </span>
                </td>
                <td>
                    <span class="badge
                        @if($incident->status == 'Resolved') bg-success
                        @elseif($incident->status == 'In Progress') bg-primary
                        @elseif($incident->status == 'Rejected') bg-danger
                        @else bg-secondary @endif">
                        {{ $incident->status }}
                    </span>
                </td>
                <td>{{ $incident->incident_date->format('M j, Y') }}</td>
                <td>{{ $incident->created_at->format('M j, Y') }}</td>
                <td class="text-center">{{ $incident->mediaEvidence->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 40px; page-break-inside: avoid;">
        {{-- <p style="text-align: center; font-weight: bold; margin-bottom: 30px;">SIGNATORIES</p> --}}

        <div style="display: flex; justify-content: center;">
            <div style="text-align: center; width: 250px;">
                <div style="border-top: 1px solid #333; width: 100%; margin-bottom: 5px;"></div>
                <p style="margin: 5px 0; font-weight: bold;">DENR CENRO Officer</p>
                <p style="margin: 5px 0; font-size: 10px;">Signature over Printed Name</p>
                <p style="margin: 5px 0; font-size: 10px;">Date: _______________</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Total Records: {{ $totalCount }} | EcoConnect - DENR CENRO Sanchez Mira</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Close window after printing
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
