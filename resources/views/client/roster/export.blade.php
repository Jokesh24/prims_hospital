<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<?php echo '<?mso-application progid="Excel.Sheet"?>' . "\n"; ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:html="http://www.w3.org/TR/REC-html40">
    
    <Styles>
        <Style ss:ID="MainHeader">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Arial" ss:Size="16" ss:Bold="1" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#0F172A" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="SubHeader">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Arial" ss:Size="12" ss:Bold="1" ss:Color="#334155"/>
            <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
        </Style>
        <Style ss:ID="DateRow">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
            <Font ss:FontName="Arial" ss:Size="10" ss:Italic="1" ss:Color="#64748B"/>
        </Style>
        <Style ss:ID="ColHeader">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#1E293B" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
            </Borders>
        </Style>
        <Style ss:ID="ColHeaderWeekend">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#E11D48"/>
            <Interior ss:Color="#FFF1F2" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
            </Borders>
        </Style>
        <Style ss:ID="CellNormal">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>
        <Style ss:ID="CellShift">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10" ss:Color="#166534" ss:Bold="1"/>
            <Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/>
            </Borders>
        </Style>
        <Style ss:ID="CellWeekOff">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#991B1B"/>
            <Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FCA5A5"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FCA5A5"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FCA5A5"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FCA5A5"/>
            </Borders>
        </Style>
        <Style ss:ID="CellWeekend">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
            <Font ss:FontName="Arial" ss:Size="10"/>
            <Interior ss:Color="#FFF1F2" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>
    </Styles>

    @foreach ($groupedEmployees as $deptName => $deptEmployees)
    @php
        $safeSheetName = substr(preg_replace('/[\[\]\*\/\\\\?\:]/', '', $deptName), 0, 31);
    @endphp
    
    <Worksheet ss:Name="{{ $safeSheetName }}">
        <Table>
            <Column ss:Width="40"/>
            <Column ss:Width="80"/>
            <Column ss:Width="180"/>
            @for ($day = 1; $day <= $daysInMonth; $day++)
                <Column ss:Width="70"/>
            @endfor

            <Row ss:Height="30">
                <Cell ss:MergeAcross="{{ $daysInMonth + 2 }}" ss:StyleID="MainHeader">
                    <Data ss:Type="String">{{ strtoupper($clientName) }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="25">
                <Cell ss:MergeAcross="{{ $daysInMonth + 2 }}" ss:StyleID="SubHeader">
                    <Data ss:Type="String">MONTHLY SHIFT ROSTER REPORT : {{ strtoupper($monthName) }} {{ $year }}</Data>
                </Cell>
            </Row>
            <Row ss:Height="20">
                <Cell ss:MergeAcross="{{ $daysInMonth + 2 }}" ss:StyleID="DateRow">
                    <Data ss:Type="String">Report Generated On: {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</Data>
                </Cell>
            </Row>
            
            <Row ss:Height="10"></Row>

            <Row ss:Height="30">
                <Cell ss:StyleID="ColHeader"><Data ss:Type="String">#</Data></Cell>
                <Cell ss:StyleID="ColHeader"><Data ss:Type="String">EMP ID</Data></Cell>
                <Cell ss:StyleID="ColHeader"><Data ss:Type="String">NAME &amp; DESIGNATION</Data></Cell>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $cDate = \Carbon\Carbon::create($year, $month, $day);
                        $style = $cDate->isWeekend() ? 'ColHeaderWeekend' : 'ColHeader';
                    @endphp
                    <Cell ss:StyleID="{{ $style }}">
                        <Data ss:Type="String">{{ sprintf('%02d', $day) }}&#10;{{ strtoupper($cDate->format('D')) }}</Data>
                    </Cell>
                @endfor
            </Row>

            @php $sno = 1; @endphp
            @foreach ($deptEmployees as $employee)
            <Row ss:Height="40">
                <Cell ss:StyleID="CellNormal"><Data ss:Type="String">{{ $sno++ }}</Data></Cell>
                <Cell ss:StyleID="CellNormal"><Data ss:Type="String">{{ $employee->employee_id }}</Data></Cell>
                <Cell ss:StyleID="CellNormal">
                    <Data ss:Type="String">{{ $employee->employee_name }}&#10;({{ $employee->designationInfo->designation_name ?? 'N/A' }})</Data>
                </Cell>

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dayStr = sprintf('%02d', $day);
                        $rosterKey = $employee->id . '_' . $dayStr;
                        $savedShift = isset($existingRosters[$rosterKey]) ? $existingRosters[$rosterKey]->shift_id : '';
                        $isWeekOff = isset($existingRosters[$rosterKey]) && $existingRosters[$rosterKey]->is_weekoff;
                        $cDate = \Carbon\Carbon::create($year, $month, $day);

                        $styleId = 'CellNormal';
                        if ($cDate->isWeekend()) {
                            $styleId = 'CellWeekend';
                        }

                        $displayVal = '';
                        if ($isWeekOff) {
                            $displayVal = 'W/O';
                            $styleId = 'CellWeekOff';
                        } elseif (!empty($savedShift) && isset($shifts[$savedShift])) {
                            $shiftData = $shifts[$savedShift];
                            $timeStr = date('H:i', strtotime($shiftData->login_time)) . '-' . date('H:i', strtotime($shiftData->logout_time));
                            
                            // Use htmlspecialchars manually on the variable to prevent breaking the XML, 
                            // while safely appending the raw &#10; entity for the line break
                            $displayVal = htmlspecialchars($shiftData->shift_name) . "&#10;(" . $timeStr . ")";
                            
                            $styleId = 'CellShift';
                        }
                    @endphp
                    <Cell ss:StyleID="{{ $styleId }}">
                        <Data ss:Type="String">{!! $displayVal !!}</Data>
                    </Cell>
                @endfor
            </Row>
            @endforeach
        </Table>
    </Worksheet>
    @endforeach
</Workbook>