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