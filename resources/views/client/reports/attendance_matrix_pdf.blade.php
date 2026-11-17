<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Register - {{ $matrix['startDate'] }} to {{ $matrix['endDate'] }}</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @page {
            size: A2 landscape;
            margin: 6mm 4mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 7.5pt;
            width: 100%;
        }

        #pdfContentArea {
            width: 100%;
            margin: 0;
            padding: 4px;
            page-break-after: avoid !important;
        }

        /* Header Grid */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 6px;
        }
        .layout-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .company-name {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 8pt;
            color: #475569;
            margin-top: 1px;
        }

        /* Legend */
        .legend-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .legend-table td {
            padding: 3px 6px;
            font-size: 7pt;
            border: none;
            vertical-align: middle;
            white-space: nowrap;
        }
        .legend-badge {
            display: inline-block;
            font-weight: 700;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6.5pt;
            margin-right: 2px;
        }

        /* Department Section */
        .dept-wrapper {
            margin-bottom: 12px;
            page-break-inside: avoid;
            width: 100%;
        }
        .dept-wrapper:last-child {
            margin-bottom: 0 !important;
            page-break-after: avoid !important;
        }
        .dept-title-box {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8pt;
            font-weight: 700;
            padding: 4px 8px;
            letter-spacing: 0.5px;
        }

        /* Matrix Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            background: #fff;
        }
        .data-table th,
        .data-table td {
            border: 0.5pt solid #cbd5e1;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            font-size: 6.5pt;
        }

        /* Unified Stacked Employee Column */
        .col-sno {
            width: 24px;
            min-width: 24px;
            color: #64748b;
        }
        .col-emp-stacked {
            min-width: 140px;
            max-width: 160px;
            text-align: left !important;
            padding-left: 5px !important;
            line-height: 1.2;
        }
        .emp-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 7.5pt;
        }
        .emp-meta {
            font-size: 6pt;
            color: #64748b;
            display: block;
        }

        /* Summary Metrics */
        .col-metric {
            width: 24px;
            min-width: 24px;
            font-weight: 700;
            font-size: 7pt;
        }

        /* Date Column Header */
        .col-date {
            min-width: 44px;
            padding: 2px 1px !important;
        }
        .date-num {
            font-size: 7pt;
            font-weight: 700;
            display: block;
        }
        .date-day {
            font-size: 5.5pt;
            font-weight: 500;
            text-transform: uppercase;
            color: #64748b;
        }
        .sunday-th {
            background-color: #fff1f2 !important;
            color: #be123c !important;
        }
        .sunday-th .date-day {
            color: #be123c !important;
        }

        /* Punch Time Label */
        .time-label {
            font-size: 5.5pt;
            color: #334155;
            display: block;
            line-height: 1;
            margin-top: 1px;
            font-family: monospace;
            letter-spacing: -0.3px;
        }

        /* Status Pills */
        .status-p { background-color: #dcfce7; color: #166534; }
        .status-a { background-color: #fee2e2; color: #991b1b; }
        .status-h { background-color: #fef3c7; color: #b45309; }
        .status-l { background-color: #dbeafe; color: #1d4ed8; }
        .status-pl { background-color: #d1fae5; color: #065f46; }
        .status-ul { background-color: #ffe4e6; color: #9f1239; }
        .status-hol { background-color: #cffafe; color: #155e75; }
        .status-w { color: #94a3b8; font-weight: 700; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 10px; display: flex; justify-content: flex-end; gap: 8px;">
        <button id="btnDownloadPDF" 
                style="padding: 6px 14px; background: #16a34a; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 8.5pt; font-weight: 700;">
            ⬇ Download Full PDF
        </button>
        <button onclick="window.print()" 
                style="padding: 6px 14px; background: #0f172a; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 8.5pt; font-weight: 700;">
            🖨 Print Document
        </button>
    </div>

    <div id="pdfContentArea">
        <!-- Header -->
        <table class="layout-table">
            <tr>
                <td style="width: 70%;">
                    <div class="company-name">{{ $matrix['user']->company_name ?? 'PRIMS HRMS' }}</div>
                    <div class="report-subtitle">
                        <strong>Consolidated Attendance Register:</strong> {{ $matrix['startDate'] }} to {{ $matrix['endDate'] }}
                    </div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div style="font-size: 7pt; color: #64748b;">
                        Generated: {{ date('d-m-Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Legend -->
        <table class="legend-table">
            <tr>
                <td style="font-weight: 700; width: 50px; color: #0f172a;">LEGEND:</td>
                <td><span class="legend-badge status-p">[P]</span> Present</td>
                <td><span class="legend-badge status-a">[A]</span> Absent</td>
                <td><span class="legend-badge status-h">[H]</span> Half Day</td>
                <td><span class="legend-badge status-l">[L]</span> Late Login</td>
                <td><span class="legend-badge status-pl">[PL]</span> Paid Leave</td>
                <td><span class="legend-badge status-ul">[UL]</span> Unpaid Leave</td>
                <td><span class="legend-badge status-hol">[HOL]</span> Holiday</td>
                <td><span class="legend-badge" style="background:#e2e8f0; color:#475569;">[W]</span> Week Off</td>
            </tr>
        </table>

        @forelse($matrix['departments'] as $deptName => $employees)
            <div class="dept-wrapper">
                <div class="dept-title-box">
                    DEPARTMENT: {{ strtoupper($deptName) }} &nbsp;·&nbsp; {{ count($employees) }} Members
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-sno">#</th>
                            <th class="col-emp-stacked">Employee Details</th>
                            <!-- Summary Metrics -->
                            <th class="col-metric" style="background: #dcfce7; color: #166534;" title="Present">P</th>
                            <th class="col-metric" style="background: #fee2e2; color: #991b1b;" title="Absent">A</th>
                            <th class="col-metric" style="background: #fef3c7; color: #b45309;" title="Half Day">H</th>
                            <th class="col-metric" style="background: #dbeafe; color: #1d4ed8;" title="Late Login">L</th>

                            <!-- Dates -->
                            @foreach($matrix['dateRange'] as $dStr)
                                @php
                                    $dObj = \Carbon\Carbon::parse($dStr);
                                    $isSun = $dObj->isSunday();
                                @endphp
                                <th class="col-date {{ $isSun ? 'sunday-th' : '' }}">
                                    <span class="date-num">{{ $dObj->format('d') }}</span>
                                    <span class="date-day">{{ $dObj->format('D') }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $index => $emp)
                            <tr>
                                <td class="col-sno">{{ $index + 1 }}</td>
                                <td class="col-emp-stacked">
                                    <span class="emp-name">{{ $emp['name'] }}</span>
                                    <span class="emp-meta">{{ $emp['id_code'] }} &bull; {{ $emp['designation'] }}</span>
                                </td>

                                <!-- Metrics -->
                                <td class="col-metric" style="background: #f0fdf4; color: #166534;">{{ $emp['summary']['P'] }}</td>
                                <td class="col-metric" style="background: #fef2f2; color: #991b1b;">{{ $emp['summary']['A'] }}</td>
                                <td class="col-metric" style="background: #fffbeb; color: #b45309;">{{ $emp['summary']['H'] }}</td>
                                <td class="col-metric" style="background: #eff6ff; color: #1d4ed8;">{{ $emp['summary']['L'] }}</td>

                                <!-- Date Cells -->
                                @foreach($matrix['dateRange'] as $dStr)
                                    @php
                                        $day = $emp['days'][$dStr];
                                        $st = $day['status'];
                                    @endphp
                                    <td>
                                        @if(in_array($st, ['P', 'L', 'H']))
                                            <span class="legend-badge {{ $st === 'P' ? 'status-p' : ($st === 'L' ? 'status-l' : 'status-h') }}">{{ $st }}</span>
                                            @php
                                                $inShort = !empty($day['in']) && $day['in'] !== '--' ? str_replace([' AM', ' PM'], ['A', 'P'], $day['in']) : '';
                                                $outShort = !empty($day['out']) && $day['out'] !== '--' ? str_replace([' AM', ' PM'], ['A', 'P'], $day['out']) : '';
                                            @endphp
                                            @if($inShort)
                                                <span class="time-label">{{ $inShort }}@if($outShort)-{{ $outShort }}@endif</span>
                                            @endif
                                        @elseif($st === 'PL')
                                            <span class="legend-badge status-pl">PL</span>
                                        @elseif($st === 'UL')
                                            <span class="legend-badge status-ul">UL</span>
                                        @elseif($st === 'HOL')
                                            <span class="legend-badge status-hol">HOL</span>
                                        @elseif($st === 'W')
                                            <span class="status-w">-</span>
                                        @else
                                            <span class="legend-badge status-a">A</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div style="text-align: center; padding: 25px; color: #64748b;">
                No employee attendance records found for the selected criteria.
            </div>
        @endforelse
    </div>

    <!-- Script to export complete width without cropping -->
    <script>
        document.getElementById('btnDownloadPDF').addEventListener('click', function () {
            const element = document.getElementById('pdfContentArea');
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '⏳ Generating PDF...';
            btn.disabled = true;

            const opt = {
                margin:       [6, 4, 6, 4],
                filename:     'Attendance_Register_{{ date("d_M_Y") }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a2', orientation: 'landscape' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(function () {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function (err) {
                console.error(err);
                btn.innerHTML = originalText;
                btn.disabled = false;
                window.print();
            });
        });
    </script>
</body>
</html>