<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Earnings;
use App\Models\Deductions;

class EarningsDeductionsController extends Controller
{
    /* -------------------------------
       AUTH HELPERS
    --------------------------------*/
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


    public function manageComponents()
    {
        try {

            $this->authorizeAccess();
            $userId = $this->getLoggedInUser()->id;

            $earnings = Earnings::where('created_by', $userId)
                ->whereNull('employee_id')
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();

            $deductions = Deductions::where('created_by', $userId)
                ->whereNull('employee_id')
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();

            return view('client.manageEarningsDeductions', compact('earnings', 'deductions'));
        } catch (\Throwable $th) {

            return back()->with('error', 'Failed to load data');
        }
    }
    /* -------------------------------
       LOAD PAGE (CREATE / MANAGE)
    --------------------------------*/
    public function createComponent()
    {
        try {

            $this->authorizeAccess();
            $userId = $this->getLoggedInUser()->id;

             $earnings = Earnings::where('created_by', $userId)
                ->whereNull('employee_id')
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();

            $deductions = Deductions::where('created_by', $userId)
                ->whereNull('employee_id')
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();

            return view('client.addEarningsDeductions', compact('earnings', 'deductions'));
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to load page.');
        }
    }

    /* -------------------------------
       STORE + UPDATE (BULK SAVE)
    --------------------------------*/
    public function storeComponent(Request $request)
    {
        try {

            $this->authorizeAccess();
            $userId = $this->getLoggedInUser()->id;

            /* ========= EARNINGS ========= */
            if ($request->earnings) {

                foreach ($request->earnings as $row) {

                    if (empty($row['title']))
                        continue;

                    $data = [
                        'earnings_title' => $row['title'],
                        'value_type' => $row['value_type'],
                        'earnings_percent' => $row['value_type'] == 1 ? ($row['percent'] ?? null) : null,
                        'earnings_money' => $row['value_type'] == 2 ? ($row['amount'] ?? null) : null,
                        'updated_by' => $userId,
                    ];

                    if (!empty($row['id'])) {
                        Earnings::where('id', $row['id'])->update($data);
                    } else {
                        $data['created_by'] = $userId;
                        $data['status'] = 1;
                        $data['trash'] = 0;

                        Earnings::create($data);
                    }
                }
            }

            /* ========= DEDUCTIONS ========= */
            if ($request->deductions) {

                foreach ($request->deductions as $row) {

                    if (empty($row['title']))
                        continue;

                    $data = [
                        'deduction_title' => $row['title'],
                        'value_type' => $row['value_type'],
                        'deduction_percent' => $row['value_type'] == 1 ? ($row['percent'] ?? null) : null,
                        'deduction_money' => $row['value_type'] == 2 ? ($row['amount'] ?? null) : null,
                        'updated_by' => $userId,
                    ];

                    if (!empty($row['id'])) {
                        Deductions::where('id', $row['id'])->update($data);
                    } else {
                        $data['created_by'] = $userId;
                        $data['status'] = 1;
                        $data['trash'] = 0;

                        Deductions::create($data);
                    }
                }
            }

            return redirect()
                ->route('manageComponents')
                ->with('success', 'Saved successfully');
        } catch (\Throwable $th) {
            return back()
                ->with('error', 'Failed to save data')
                ->withInput();
        }
    }

    public function editComponent(Request $request, $id)
    {
        return redirect()->route('createComponent');
    }

    /* -------------------------------
       DELETE COMPONENT
    --------------------------------*/
    public function deleteComponent(Request $request, $id)
    {
        try {

            $this->authorizeAccess();

            $type = $request->type;

            if ($type == 'earning') {
                Earnings::where('id', $id)->update(['trash' => 1]);
            } elseif ($type == 'deduction') {
                Deductions::where('id', $id)->update(['trash' => 1]);
            } else {
                abort(404);
            }

            return redirect()
                ->route('manageComponents')
                ->with('success', 'Deleted successfully');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Delete failed'
            ]);
        }
    }
}
