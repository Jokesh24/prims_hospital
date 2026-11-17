<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }

        .date-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .date-title {
            background: #1e3a8a;
            color: #fff;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 13px;
            border-radius: 4px 4px 0 0;
        }

        .dept-title {
            background: #e0e7ff;
            color: #3730a3;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
            border-left: 3px solid #3730a3;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }

        .status-present {
            color: #16a34a;
            font-weight: bold;
        }

        .status-absent {
            color: #dc2626;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 6px 16px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            Print / Save to PDF
        </button>
    </div>

    <div class="header">
        <h2>{{ $user->company_name ?? 'School HRMS' }}</h2>
        <p><strong>Attendance Report:</strong> {{ $fromDate }} to {{ $toDate }}</p>
    </div>

    @forelse($groupedData as $date => $departments)
        <div class="date-section">
            <div class="date-title">DATE: {{ $date }}</div>

            @foreach($departments as $deptName => $employees)
                <div class="dept-title">Department: {{ $deptName }}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Employee Name</th>
                            <th style="width: 120px;" class="text-center">Login Time</th>
                            <th style="width: 120px;" class="text-center">Logout Time</th>
                            <th style="width: 100px;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $index => $emp)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $emp['emp_name'] }} <small style="color:#64748b;">({{ $emp['emp_id'] }})</small></td>
                                <td class="text-center">{{ $emp['login_time'] }}</td>
                                <td class="text-center">{{ $emp['logout_time'] }}</td>
                                <td class="text-center {{ $emp['status'] == 'Present' ? 'status-present' : 'status-absent' }}">
                                    {{ $emp['status'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    @empty
        <p style="text-align: center; padding: 20px; color: #64748b;">No attendance records found for the selected period.
        </p>
    @endforelse

</body>

</html>