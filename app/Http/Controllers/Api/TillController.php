<?php

namespace App\Http\Controllers\Api;

use App\Models\Till;
use App\Models\TillTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TillController extends Controller
{
    /**
     * Open a new till for the user.
     */
    public function open(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        // Check if user already has an open till
        $openTill = Till::where('user_id', auth()->id())
            ->where('warehouse_id', $request->warehouse_id)
            ->whereNull('closed_at')
            ->first();

        if ($openTill) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an open till. Close it before opening a new one.',
            ], 400);
        }

        $till = Till::create([
            'warehouse_id' => $request->warehouse_id,
            'user_id' => auth()->id(),
            'opening_balance' => $request->opening_balance,
            'opened_at' => now(),
            'transactions_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Till opened',
            'till' => $till,
        ], 201);
    }

    /**
     * Get the current open till for the user.
     */
    public function current(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $till = Till::where('user_id', auth()->id())
            ->where('warehouse_id', $request->warehouse_id)
            ->whereNull('closed_at')
            ->first();

        if (!$till) {
            return response()->json([
                'success' => false,
                'message' => 'No open till found',
            ], 404);
        }

        $till->load('transactions');
        $till->transaction_sum = $till->transactions->sum('amount');

        return response()->json([
            'success' => true,
            'till' => $till,
        ]);
    }

    /**
     * Record a transaction in the till.
     */
    public function recordTransaction(Request $request)
    {
        $request->validate([
            'till_id' => 'required|exists:tills,id',
            'type' => 'required|in:sale,refund,deposit,withdrawal',
            'amount' => 'required|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $till = Till::findOrFail($request->till_id);

            // Verify till belongs to authenticated user
            if ($till->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }

            // Check till is open
            if ($till->closed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Till is closed',
                ], 400);
            }

            TillTransaction::create([
                'till_id' => $till->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'order_id' => $request->order_id,
                'description' => $request->description,
            ]);

            $till->increment('transactions_count');

            return response()->json([
                'success' => true,
                'message' => 'Transaction recorded',
            ], 201);
        });
    }

    /**
     * Get till transactions.
     */
    public function getTransactions(Request $request, Till $till)
    {
        // Verify till belongs to authenticated user
        if ($till->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $transactions = $till->transactions()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
            'total_amount' => $transactions->sum('amount'),
        ]);
    }

    /**
     * Close the till with reconciliation.
     */
    public function close(Request $request, Till $till)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
        ]);

        // Verify till belongs to authenticated user
        if ($till->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($till->closed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Till is already closed',
            ], 400);
        }

        // Calculate expected balance
        $transactionSum = $till->transactions->sum('amount');
        $expectedBalance = $till->opening_balance + $transactionSum;
        $variance = $request->closing_balance - $expectedBalance;

        $till->update([
            'closing_balance' => $request->closing_balance,
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Till closed',
            'reconciliation' => [
                'opening_balance' => $till->opening_balance,
                'transactions_total' => $transactionSum,
                'expected_balance' => $expectedBalance,
                'actual_closing_balance' => $request->closing_balance,
                'variance' => $variance,
                'variance_percentage' => round(($variance / $expectedBalance) * 100, 2) . '%',
            ],
        ]);
    }
}

