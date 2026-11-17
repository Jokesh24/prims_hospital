<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRecovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeAdvance;

class AdvancesAndArrearsController extends Controller
{


    private function authorizeAccess()
    {
        if (!session()->has('ClientUser')) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();

        if (!$client) {
            session()->forget('ClientUser');
            abort(403, 'Unauthorized access.');
        }
    }
    private function getLoggedInUser()
    {
        return Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();
    }
    public function index()
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        $advances = EmployeeAdvance::with('employee')
            ->where('client_id', $user->id)
            ->where('trash', 0)
            ->latest()
            ->paginate(20);
        $employees = Employee::where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('employee_name')
            ->get();

        return view(
            'client.manageAdvances',
            compact('advances', 'employees')
        );
    }

    public function store(Request $request)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $request->validate([

                'employee_id' => 'required|exists:rl_employees_tbl,id',

                'advance_date' => 'required|date',

                'advance_amount' => 'required|numeric|min:1',

                'recovery_type' => 'required',

                'monthly_recovery_amount' => 'nullable|numeric|min:0',

                'payment_mode' => 'required',

                'reference_number' => 'nullable|max:100',

                'remarks' => 'nullable',

            ]);

            EmployeeAdvance::create([

                'client_id' => $user->id,

                'employee_id' => $request->employee_id,

                'advance_date' => $request->advance_date,

                'advance_amount' => $request->advance_amount,

                'recovered_amount' => 0,

                'balance_amount' => $request->advance_amount,

                'recovery_type' => $request->recovery_type,

                'monthly_recovery_amount' => $request->monthly_recovery_amount,

                'payment_mode' => $request->payment_mode,

                'reference_number' => $request->reference_number,

                'remarks' => $request->remarks,

                'status' => 1,

                'trash' => 0,

                'created_by' => $user->id,

            ]);

            return redirect()
                ->route('advances')
                ->with(
                    'success',
                    'Advance added successfully.'
                );

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to save advance.'
                );
        }
    }
    public function edit($id)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        $advance = EmployeeAdvance::where(
            'client_id',
            $user->id
        )->findOrFail($id);

        $employees = Employee::where(
            'client_id',
            $user->id
        )
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('employee_name')
            ->get();

        return view(
            'client.editAdvance',
            compact(
                'advance',
                'employees'
            )
        );
    }

    public function update(Request $request, $id)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $advance = EmployeeAdvance::where(
                'client_id',
                $user->id
            )->findOrFail($id);

            $request->validate([

                'employee_id' => 'required',

                'advance_date' => 'required|date',

                'advance_amount' => 'required|numeric|min:1',

                'recovery_type' => 'required',

                'monthly_recovery_amount' => 'nullable|numeric|min:0',

                'payment_mode' => 'required',

                'reference_number' => 'nullable|max:100',

                'remarks' => 'nullable',

            ]);

            $advance->update([

                'employee_id' => $request->employee_id,

                'advance_date' => $request->advance_date,

                'advance_amount' => $request->advance_amount,

                'balance_amount' =>
                    $request->advance_amount -
                    $advance->recovered_amount,

                'recovery_type' => $request->recovery_type,

                'monthly_recovery_amount' => $request->monthly_recovery_amount,

                'payment_mode' => $request->payment_mode,

                'reference_number' => $request->reference_number,

                'remarks' => $request->remarks,

                'updated_by' => $user->id,

            ]);

            return redirect()
                ->route('advances')
                ->with(
                    'success',
                    'Advance updated successfully.'
                );

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update advance.'
                );
        }
    }

    public function delete($id)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            EmployeeAdvance::where('client_id', $user->id)
                ->where('id', $id)
                ->update([
                    'trash' => 1,
                    'updated_by' => $user->id,

                ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Advance deleted successfully.'
                );

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to delete advance.'
                );
        }
    }

    public function manageRecovery($advanceId)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $advance = EmployeeAdvance::with('employee')
                ->where('client_id', $user->id)
                ->where('id', $advanceId)
                ->firstOrFail();

            $recoveries = $advance->recoveries()
                ->latest('recovery_date')
                ->paginate(20);

            return view(
                'client.manageAdvanceRecovery',
                compact(
                    'advance',
                    'recoveries'
                )
            );

        } catch (\Exception $e) {
            Log::error('Store Recovery Error', [

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return redirect()
                ->route('advances')
                ->with(
                    'error',
                    'Unable to load advance recovery details.'
                );
        }
    }
    public function storeRecovery(Request $request)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $request->validate([

                'advance_id' => 'required|exists:rl_employee_advances_tbl,id',

                'recovery_date' => 'required|date',

                'recovered_amount' => 'required|numeric|min:1',

                'payment_mode' => 'required',

                'reference_number' => 'nullable|max:100',

                'remarks' => 'nullable',

            ]);

            $advance = EmployeeAdvance::where(
                'client_id',
                $user->id
            )->findOrFail($request->advance_id);

            if ($advance->balance_amount <= 0) {

                return back()->with(
                    'error',
                    'Advance is already fully recovered.'
                );
            }

            $recoverAmount = min(
                $request->recovered_amount,
                $advance->balance_amount
            );

            $balanceBefore = $advance->balance_amount;

            $balanceAfter = $balanceBefore - $recoverAmount;

            $advance->recoveries()->create([

                'client_id' => $user->id,

                'employee_id' => $advance->employee_id,

                'payroll_id' => null,

                'recovery_date' => $request->recovery_date,

                'recovered_amount' => $recoverAmount,

                'balance_before' => $balanceBefore,

                'balance_after' => $balanceAfter,

                'recovery_type' => 2,

                'payment_mode' => $request->payment_mode,

                'reference_number' => $request->reference_number,

                'remarks' => $request->remarks,

                'created_by' => $user->id,

            ]);

            $advance->update([

                'recovered_amount' =>
                    $advance->recovered_amount + $recoverAmount,

                'balance_amount' =>
                    $balanceAfter,

                'updated_by' =>
                    $user->id,

            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Recovery added successfully.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to save recovery.'
                );
        }
    }
    public function updateRecovery(Request $request, $id)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $request->validate([

                'recovery_date' => 'required|date',

                'recovered_amount' => 'required|numeric|min:1',

                'payment_mode' => 'required',

                'reference_number' => 'nullable|max:100',

                'remarks' => 'nullable',

            ]);

            $recovery = AdvanceRecovery::findOrFail($id);

            $advance = EmployeeAdvance::where(
                'client_id',
                $user->id
            )->findOrFail($recovery->advance_id);

            /*
            |--------------------------------------------------------------------------
            | Rollback Previous Recovery
            |--------------------------------------------------------------------------
            */

            $advance->recovered_amount -=
                $recovery->recovered_amount;

            $advance->balance_amount +=
                $recovery->recovered_amount;

            /*
            |--------------------------------------------------------------------------
            | Apply New Recovery
            |--------------------------------------------------------------------------
            */

            $recoverAmount = min(

                $request->recovered_amount,

                $advance->balance_amount

            );

            $balanceBefore = $advance->balance_amount;

            $balanceAfter = $balanceBefore - $recoverAmount;

            $recovery->update([

                'recovery_date' => $request->recovery_date,

                'recovered_amount' => $recoverAmount,

                'balance_before' => $balanceBefore,

                'balance_after' => $balanceAfter,

                'payment_mode' => $request->payment_mode,

                'reference_number' => $request->reference_number,

                'remarks' => $request->remarks,

                'updated_by' => $user->id,

            ]);

            $advance->update([

                'recovered_amount' =>
                    $advance->recovered_amount + $recoverAmount,

                'balance_amount' =>
                    $balanceAfter,

                'updated_by' =>
                    $user->id,

            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Recovery updated successfully.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update recovery.'
                );
        }
    }
}