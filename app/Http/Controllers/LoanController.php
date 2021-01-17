<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function createLoan(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required',
                'date_collected' => 'required',
                'date_due' => 'required',
                'duration' => 'required'
            ]);

            $active_loan = $request->user()->active_loan();

            if($active_loan) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'an active loan exists'
                ], 400);
            }

            $loan = Loan::create([
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'date_collected' => $request->date_collected,
                'date_due' => $request->date_due,
                'duration' => $request->duration,
                'status' => 'active'
            ]);

            return response()->json([
                'data' => [ 
                    'loan' => $loan
                ],
                'status' => 'success',
                'message' => 'loan created successfully'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 400);
        }  
    }
}
