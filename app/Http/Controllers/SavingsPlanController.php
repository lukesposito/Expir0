<?php

namespace App\Http\Controllers;

use App\Models\SavingsPlan;
use App\Services\Forecasting\ExpenseForecastService;
use Illuminate\Http\Request;

class SavingsPlanController extends Controller
{
    public function store(
        Request $request,
        ExpenseForecastService $expenseForecastService
    ) {
        $forecast = $expenseForecastService->build(12);
        $peakMonth = $forecast['peak_month'] ?? null;

        if (!$peakMonth || empty($peakMonth['total'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non ci sono ancora uscite future sufficienti per creare un piano.',
            ], 422);
        }

        $plan = SavingsPlan::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'target_month' => $peakMonth['month_number'],
                'target_year' => $peakMonth['year'],
            ],
            [
                'target_amount' => $peakMonth['total'],
                'saved_amount' => 0,
                'monthly_suggestion' => $forecast['suggested_monthly_reserve'],
                'status' => 'active',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Piano di accantonamento creato per ' . $peakMonth['label'] . '.',
            'plan' => $plan,
        ]);
    }

    public function addContribution(
        Request $request,
        SavingsPlan $savingsPlan
    ) {
        if (
            $savingsPlan->user_id !== null &&
            $savingsPlan->user_id !== auth()->id()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Non sei autorizzato a modificare questo piano.',
            ], 403);
        }

        if ($savingsPlan->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Questo piano non è attivo.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $savingsPlan->refresh();

        $amount = isset($validated['amount'])
            ? (float) $validated['amount']
            : (float) $savingsPlan->monthly_suggestion;

        $remainingAmount = max(
            0,
            (float) $savingsPlan->target_amount
                - (float) $savingsPlan->saved_amount
        );

        if ($remainingAmount <= 0) {
            $savingsPlan->update([
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'L’obiettivo di questo piano è già stato raggiunto.',
            ], 422);
        }

        $appliedAmount = min($amount, $remainingAmount);

        $newSavedAmount = round(
            (float) $savingsPlan->saved_amount + $appliedAmount,
            2
        );

        $completed = $newSavedAmount >= (float) $savingsPlan->target_amount;

        $savingsPlan->update([
            'saved_amount' => $newSavedAmount,
            'status' => $completed ? 'completed' : 'active',
        ]);

        $savingsPlan->refresh();

        return response()->json([
            'success' => true,
            'message' => $completed
                ? 'Obiettivo raggiunto: piano completato!'
                : 'Accantonamento registrato correttamente.',
            'plan' => $savingsPlan,
            'applied_amount' => $appliedAmount,
            'remaining_amount' => $savingsPlan->remainingAmount(),
            'progress_percentage' => $savingsPlan->progressPercentage(),
        ]);
    }

    public function destroy(SavingsPlan $savingsPlan)
    {
        if (
            $savingsPlan->user_id !== null &&
            $savingsPlan->user_id !== auth()->id()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Non sei autorizzato a modificare questo piano.',
            ], 403);
        }

        $savingsPlan->update([
            'status' => 'dismissed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Piano di accantonamento disattivato.',
        ]);
    }
}