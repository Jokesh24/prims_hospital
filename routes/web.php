<?php

use App\Http\Controllers\employee\LeavesController;
use App\Http\Controllers\employee\ProfileController;
use Illuminate\Support\Facades\Route;

/* Import all required controllers at the top */
use App\Http\Controllers\client\LoginController;
use App\Http\Controllers\client\DashboardController;
use App\Http\Controllers\client\DepartmentController;
use App\Http\Controllers\client\EmployeeController;
use App\Http\Controllers\client\HolidayController;
use App\Http\Controllers\client\PaidLeavesController;
use App\Http\Controllers\client\LeavesController as ClientLeaveController;

use App\Http\Controllers\client\AttendanceController;
use App\Http\Controllers\client\EarningsDeductionsController;
use App\Http\Controllers\client\PayslipController;
use App\Http\Controllers\client\AdvancesAndArrearsController;
use App\Http\Controllers\client\AdminController;
use App\Http\Controllers\client\EmployeeShiftAssignmentController;





Route::get('/', function () {
    return redirect()->route('client.login');
});

/* ═══════════════ CLIENT MODULE ═══════════════ */
Route::group(['prefix' => 'client'], function () {
    // Route::middleware('guest:client')->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('client.login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('client.authenticate');
    // });
    Route::middleware(['auth:client,employee', 'nocache'])->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('client.logout');
        /* --- Dashboard --- */
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('client.dashboard');
        /* --- Holiday Management --- */
        Route::get('/holidays', [HolidayController::class, 'manage'])->name('client.holidays');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{id}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::post('/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        /* --- Paid Leaves Management --- */
        Route::get('/paidleaves', [PaidLeavesController::class, 'manage'])->name('client.paidleaves');
        Route::post('/paidleaves', [PaidLeavesController::class, 'store'])->name('paidleaves.store');
        Route::put('/paidleaves/{id}', [PaidLeavesController::class, 'update'])->name('paidleaves.update');
        Route::delete('/paidleaves/{id}', [PaidLeavesController::class, 'destroy'])->name('paidleaves.destroy');
        Route::post('/paidleaves/bulk-department', [PaidLeavesController::class, 'storeBulkByDepartment'])->name('paidleaves.storeBulkByDepartment');
        /* --- Department Management --- */
        Route::get('/departments', [DepartmentController::class, 'manage'])->name('client.departments');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::post('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        /* --- Sub Departments --- */
        Route::get('/sub-departments', [App\Http\Controllers\client\SubDepartmentController::class, 'manage'])->name('client.sub_departments');
        Route::post('/sub-departments', [App\Http\Controllers\client\SubDepartmentController::class, 'store'])->name('sub_departments.store');
        Route::put('/sub-departments/{id}', [App\Http\Controllers\client\SubDepartmentController::class, 'update'])->name('sub_departments.update');
        Route::post('/sub-departments/{id}/destroy', [App\Http\Controllers\client\SubDepartmentController::class, 'destroy'])->name('sub_departments.destroy');
        /* --- Employee Directory --- */
        Route::get('/employees', [EmployeeController::class, 'manage'])->name('client.employees');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::get('/employees/reset-passwords', [EmployeeController::class, 'resetAllEmployeePasswords'])
            ->name('employees.reset.passwords');
        Route::get('/employees/export-excel', [EmployeeController::class, 'exportEmployeesExcel'])->name('employees.export.excel');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/get-designations-by-department/{department_id}', [EmployeeController::class, 'getByDepartment'])->name('client.get.designations');

        Route::get('/manage-roster', [\App\Http\Controllers\client\RosterController::class, 'manageRoster'])->name('roster.manage');
        Route::post('/save-roster', [\App\Http\Controllers\client\RosterController::class, 'saveRoster'])->name('roster.save');
        Route::get('/roster/export', [App\Http\Controllers\client\RosterController::class, 'exportRoster'])->name('roster.export');
        Route::post('/roster/reassign-ajax', [App\Http\Controllers\client\RosterController::class, 'reassignShiftAjax'])->name('roster.reassignAjax');
        // Shift Assignment
        Route::get('/employee-shifts', [EmployeeShiftAssignmentController::class, 'index'])->name('client.employee_shifts');
        Route::get('/employee-shifts/dependencies', [EmployeeShiftAssignmentController::class, 'getDependencies'])->name('client.employee_shifts.dependencies');
        Route::get('/employee-shifts/data', [EmployeeShiftAssignmentController::class, 'getEmployeesAndShifts'])->name('client.employee_shifts.data');
        Route::post('/employee-shifts/store', [EmployeeShiftAssignmentController::class, 'store'])->name('client.employee_shifts.store');
        Route::get('/employee-shifts/view-roster', [EmployeeShiftAssignmentController::class, 'assignedShifts'])->name('client.employee_shifts.view');

        Route::get('/manage-shifts', [\App\Http\Controllers\client\ShiftController::class, 'manage'])->name('shifts.manage');
        Route::post('/shifts/store', [\App\Http\Controllers\client\ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/update/{id}', [\App\Http\Controllers\client\ShiftController::class, 'update'])->name('shifts.update');
        Route::post('/shifts/destroy/{id}', [\App\Http\Controllers\client\ShiftController::class, 'destroy'])->name('shifts.destroy');

        /* --- Leaves Management --- */
        Route::get('/leaves', [ClientLeaveController::class, 'manage'])->name('client.leaves.manage');
        Route::get('/pending_leaves', [ClientLeaveController::class, 'getActiveLeaves'])->name('client.pending.leaves');
        Route::get('/cancelled_rejected_leaves', [ClientLeaveController::class, 'getHistoryLeaves'])->name('client.leaves.cancelled');

        Route::post('/leaves/update-status', [ClientLeaveController::class, 'updateStatus'])->name('client.leaves.updateStatus');

        /* Leaves Management by Client */
        Route::get('/leaves', [App\Http\Controllers\client\LeaveController::class, 'index'])->name('client.leaves');
        Route::post('/leaves/store', [App\Http\Controllers\client\LeaveController::class, 'store'])->name('leaves.store');
        Route::put('/leaves/{id}/update', [App\Http\Controllers\client\LeaveController::class, 'update'])->name('leaves.update');
        Route::post('/leaves/{id}/destroy', [App\Http\Controllers\client\LeaveController::class, 'destroy'])->name('leaves.destroy');
        Route::post('/leaves/get-paid-left', [App\Http\Controllers\client\LeaveController::class, 'getPaidLeavesLeft'])->name('leaves.paid_left');
        Route::post('/leaves/update-date-status', [App\Http\Controllers\client\LeaveController::class, 'updateDateStatus'])->name('leaves.update_date_status');

        // Leave Types CRUD
        Route::post('/leave-types/store', [App\Http\Controllers\client\LeaveController::class, 'storeLeaveType'])->name('leave_types.store');
        Route::put('/leave-types/{id}/update', [App\Http\Controllers\client\LeaveController::class, 'updateLeaveType'])->name('leave_types.update');
        Route::delete('/leave-types/{id}/destroy', [App\Http\Controllers\client\LeaveController::class, 'destroyLeaveType'])->name('leave_types.destroy');

        /* Hari routes*/
        Route::get('/manageAttendance', [AttendanceController::class, 'manageAttendance'])->name('manageAttendance');
        Route::get('/createAttendance', [AttendanceController::class, 'createAttendance'])->name('createAttendance');
        Route::post('/storeAttendance', [AttendanceController::class, 'storeAttendance'])->name('storeAttendance');
        Route::get('/editAttendance/{id}', [AttendanceController::class, 'editAttendance'])->name('editAttendance');
        Route::post('/updateAttendance/{id}', [AttendanceController::class, 'updateAttendance'])->name('updateAttendance');
        Route::post('/deleteAttendance/{id}', [AttendanceController::class, 'deleteAttendance'])->name('deleteAttendance');
        Route::get('/bulkAttendance', [AttendanceController::class, 'bulkAttendance'])->name('bulkAttendance');
        Route::post('/storeBulkAttendance', [AttendanceController::class, 'storeBulkAttendance'])->name('storeBulkAttendance');
        Route::post('/checkOut/{id}', [AttendanceController::class, 'checkOutAttendance'])->name('checkOutAttendance');
        Route::post('/storeOfficeTiming', [AttendanceController::class, 'storeOfficeTiming'])->name('storeOfficeTiming');
        Route::get('/monthlyAttendance', [AttendanceController::class, 'monthlyAttendance'])->name('monthlyAttendance');
        Route::get('/managePayroll', [AttendanceController::class, 'managePayroll'])->name('managePayroll');
        Route::get('/manageOfficeTimings', [AttendanceController::class, 'manageOfficeTimings'])->name('manageOfficeTimings');
        Route::post('/saveOfficeTiming', [AttendanceController::class, 'saveOfficeTiming'])->name('saveOfficeTiming');
        Route::get('/createOfficeTimings', [AttendanceController::class, 'createOfficeTimings'])->name('createOfficeTimings');
        Route::get('/manage-attendance/export-excel', [App\Http\Controllers\client\AttendanceController::class, 'exportAttendanceExcel'])->name('exportAttendanceExcel');
        Route::get('/manage-attendance/export-pdf', [App\Http\Controllers\client\AttendanceController::class, 'exportAttendancePdf'])->name('exportAttendancePdf');
        Route::get('/attendance/monthly-entry', [AttendanceController::class, 'monthlyAttendanceEntry'])->name('monthlyAttendanceEntry');
        Route::post('/attendance/monthly-entry', [AttendanceController::class, 'storeMonthlyAttendanceSingle'])->name('storeMonthlyAttendanceSingle');

        /* Department Settings Routes */
        Route::get('/client/department-settings', [App\Http\Controllers\client\DepartmentSettingsController::class, 'index'])->name('departmentSettings');
        Route::post('/client/department-settings/{id}', [App\Http\Controllers\client\DepartmentSettingsController::class, 'update'])->name('departmentSettings.update');

        Route::post('/importEmployees', [EmployeeController::class, 'importEmployees'])->name('importEmployees');
        Route::get('/downloadTemplate', [EmployeeController::class, 'downloadTemplate'])->name('downloadTemplate');


        Route::get('/generatePayroll', [PayslipController::class, 'generatePayroll'])->name('generatePayroll');
        Route::get('/viewPayslip/{employee}/{month}/{year}', [PayslipController::class, 'viewPayslip'])->name('viewPayslip');
        Route::post('/generatePayrollForMonth', [PayslipController::class, 'generatePayrollForMonth'])->name('generatePayrollForMonth');
        Route::get('/exportPayroll', [PayslipController::class, 'exportPayroll'])->name('exportPayroll');
        Route::get('/exportBankPayroll', [PayslipController::class, 'exportBankPayroll'])->name('exportBankPayroll');
        Route::get('/exportMonthlyAttendance', [PayslipController::class, 'exportMonthlyAttendance'])->name('exportMonthlyAttendance');
        Route::get('/attendanceLogs/{attendance}', [PayslipController::class, 'attendanceLogs'])->name('attendanceLogs');

        Route::get('/employeeOT/{employee}/{month}/{year}', [PayslipController::class, 'manageEmployeeOT'])->name('manageEmployeeOT');
        Route::post('/employeeOT/store', [PayslipController::class, 'storeEmployeeOT'])->name('storeEmployeeOT');
        Route::post('/employeeOT/update/{id}', [PayslipController::class, 'updateEmployeeOT'])->name('updateEmployeeOT');
        Route::get('/otEmployeesList', [PayslipController::class, 'otEmployeesList'])->name('otEmployeesList');
        /* Date-wise OT assignment */
        Route::get('/assign-ot', [PayslipController::class, 'assignOT'])->name('assignOT');
        Route::post('/assign-ot/store', [PayslipController::class, 'storeAssignedOT'])->name('storeAssignedOT');
        Route::post('/assign-ot/date/{id}/delete', [PayslipController::class, 'destroyAssignedOTDate'])->name('destroyAssignedOTDate');
        Route::get('/employeeTDS/{employee}', [PayslipController::class, 'manageEmployeeTDS'])->name('manageEmployeeTDS');
        Route::post('/employeeTDS/update/{employee}', [PayslipController::class, 'updateEmployeeTDS'])->name('updateEmployeeTDS');
        Route::post('/advance-recovery/process', [PayslipController::class, 'processAdvanceRecovery'])->name('processAdvanceRecovery');

        /* --- Designations & Shifts --- */
        Route::get('/designations', [App\Http\Controllers\client\DesignationController::class, 'manage'])->name('client.designations');
        Route::post('/designations', [App\Http\Controllers\client\DesignationController::class, 'store'])->name('designations.store');
        Route::put('/designations/{id}', [App\Http\Controllers\client\DesignationController::class, 'update'])->name('designations.update');
        Route::post('/designations/{id}/destroy', [App\Http\Controllers\client\DesignationController::class, 'destroy'])->name('designations.destroy');

        Route::get('/manageComponents', [EarningsDeductionsController::class, 'manageComponents'])->name('manageComponents');
        Route::get('/createComponent', [EarningsDeductionsController::class, 'createComponent'])->name('createComponent');
        Route::post('/storeComponent', [EarningsDeductionsController::class, 'storeComponent'])->name('storeComponent');
        Route::get('/editComponent/{id}', [EarningsDeductionsController::class, 'editComponent'])->name('editComponent');
        Route::post('/updateComponent/{id}', [EarningsDeductionsController::class, 'updateComponent'])->name('updateComponent');
        Route::post('/deleteComponent/{id}', [EarningsDeductionsController::class, 'deleteComponent'])->name('deleteComponent');

        Route::get('/advances', [AdvancesAndArrearsController::class, 'index'])->name('advances');
        Route::get('/advances/create', [AdvancesAndArrearsController::class, 'create'])->name('advances.create');
        Route::post('/advances/store', [AdvancesAndArrearsController::class, 'store'])->name('advances.store');
        Route::get('/advances/edit/{id}', [AdvancesAndArrearsController::class, 'edit'])->name('advances.edit');
        Route::post('/advances/update/{id}', [AdvancesAndArrearsController::class, 'update'])->name('advances.update');
        Route::post('/advances/delete/{id}', [AdvancesAndArrearsController::class, 'delete'])->name('advances.delete');

        Route::get('/advanceRecovery/{advanceId}', [AdvancesAndArrearsController::class, 'manageRecovery'])->name('advanceRecovery');
        Route::post('/advanceRecovery/store', [AdvancesAndArrearsController::class, 'storeRecovery'])->name('advanceRecovery.store');
        Route::post('/advanceRecovery/update/{id}', [AdvancesAndArrearsController::class, 'updateRecovery'])->name('advanceRecovery.update');

        Route::get('/manageClients', [AdminController::class, 'manageClients'])->name('manageClients');
        Route::get('/createClient', [AdminController::class, 'createClient'])->name('createClient');
        Route::post('/storeClient', [AdminController::class, 'storeClient'])->name('storeClient');
        Route::get('/editClient/{id}', [AdminController::class, 'editClient'])->name('editClient');
        Route::post('/updateClient/{id}', [AdminController::class, 'updateClient'])->name('updateClient');
        Route::post('/deleteClient/{id}', [AdminController::class, 'deleteClient'])->name('deleteClient');

        Route::get('/change-password', [AdminController::class, 'showChangePassword'])->name('changePassword');
        Route::post('/change-password', [AdminController::class, 'updatePassword'])->name('updatePassword');
        /*Hari routes end*/


        // Login Access Management
        Route::get('/login-access', [App\Http\Controllers\client\LoginAccessController::class, 'manage'])->name('client.login_access');
        Route::get('/login-access/create', [App\Http\Controllers\client\LoginAccessController::class, 'create'])->name('login_access.create');
        Route::post('/login-access', [App\Http\Controllers\client\LoginAccessController::class, 'store'])->name('login_access.store');
        Route::get('/login-access/{id}/edit', [App\Http\Controllers\client\LoginAccessController::class, 'edit'])->name('login_access.edit');
        Route::put('/login-access/{id}', [App\Http\Controllers\client\LoginAccessController::class, 'update'])->name('login_access.update');
        Route::post('/login-access/{id}/destroy', [App\Http\Controllers\client\LoginAccessController::class, 'destroy'])->name('login_access.destroy');

        // AJAX Endpoint for cascading dropdowns
        Route::post('/login-access/fetch-employees', [App\Http\Controllers\client\LoginAccessController::class, 'fetchEmployees'])->name('login_access.fetch_employees');


        // Leave Flow Settings (Admin/HR Side)
        Route::resource('leave-flows', App\Http\Controllers\client\LeaveFlowController::class, [
            'names' => [
                'index' => 'client.leave_flows.index',
                'create' => 'client.leave_flows.create',
                'store' => 'client.leave_flows.store',
                'destroy' => 'client.leave_flows.destroy',
            ]
        ])->except(['show', 'edit', 'update']);

        // Leave Approvals (Employee Side)
        Route::get('employee/leave-approvals', [App\Http\Controllers\client\LeaveApprovalController::class, 'pendingLeaves'])->name('employee.leave_approvals');
        Route::post('employee/leave-approvals/{id}/action', [App\Http\Controllers\client\LeaveApprovalController::class, 'actionLeave'])->name('employee.leave_approvals.action');
    });
});




/* ═══════════════ Employee MODULE ═══════════════ */

use App\Http\Controllers\employee\LoginController as EmployeeLoginController;
use App\Http\Controllers\employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\employee\EmployeePayslipController;
use App\Http\Controllers\employee\EmployeeAttendanceController;


Route::group(['prefix' => 'employee'], function () {
    // Route::middleware('guest:employee')->group(function () {
    Route::get('/login', [EmployeeLoginController::class, 'login'])->name('employee.login');
    Route::post('/login', [EmployeeLoginController::class, 'authenticate'])->name('employee.authenticate');
    // });
    Route::middleware('auth:employee')->group(function () {
        Route::post('/logout', [EmployeeLoginController::class, 'logout'])->name('employee.logout');
        Route::get('/change-password', [EmployeeLoginController::class, 'showChangePasswordForm'])->name('employee.change_password');
        Route::post('/change-password', [EmployeeLoginController::class, 'updatePassword'])->name('employee.update_password');
        /* --- Dashboard --- */
        Route::get('/dashboard', [EmployeeDashboardController::class, 'dashboard'])->name('employee.dashboard');
        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('employee.profile');
        Route::post('/profile/upload-document', [ProfileController::class, 'uploadDocument'])->name('employee.upload_document');
        Route::post('/manage-skills', [ProfileController::class, 'manageSkills'])->name('employee.manage_skills');
        Route::post('/update-skill', [ProfileController::class, 'updateSkill'])->name('employee.update_skill');
        Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('employee.update_profile');
        /* --- Leaves --- */
        Route::get('/apply_leave', [LeavesController::class, 'apply_leave'])->name('employee.apply_leave');
        Route::get('/leaves', [LeavesController::class, 'manage'])->name('employee.leaves.manage');
        Route::post('/leaves/store', [LeavesController::class, 'store'])->name('leaves.store');
        // Route::post('/leaves/cancel/{id}', [LeavesController::class, 'cancel'])->name('employee.leaves.cancel');
        Route::post('/leaves/cancel', [LeavesController::class, 'cancel'])->name('employee.leaves.cancel');
        Route::get('/leaves/edit/{id}', [LeavesController::class, 'edit'])->name('employee.leaves.edit');
        Route::post('/leaves/update/{id}', [LeavesController::class, 'update'])->name('employee.leaves.update');
        /* --- PaySlips --- */
        Route::get('/payslip', [EmployeePayslipController::class, 'index'])->name('employee.payslip.index');
        Route::get('/payslip/view/{month}/{year}', [EmployeePayslipController::class, 'view'])->name('employee.payslip.view');


        /* --- Employee Routes --- */
        Route::get('/attendance', [EmployeeAttendanceController::class, 'dailyLog'])->name('employee.attendance.index');
        Route::get('/attendance/monthly', [EmployeeAttendanceController::class, 'monthlySheet'])->name('employee.attendance.monthly');



    });
});





























































































