<?php

namespace App\Services\Forecasting;

use App\Models\Deadline;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpenseForecastService
{
    public function build(int $months = 12): array
    {
        $months = max(1, min($months, 24));

        $today = now()->startOfDay();
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()
            ->addMonths($months - 1)
            ->endOfMonth();

        $monthly = collect();

        for ($offset = 0; $offset < $months; $offset++) {
            $month = $startDate->copy()->addMonths($offset);

            $monthly->put($month->format('Y-m'), [
                'key' => $month->format('Y-m'),
                'label' => $month->locale('it')->translatedFormat('F Y'),
                'month_number' => $month->month,
                'year' => $month->year,
                'total' => 0.0,
                'count' => 0,
                'items' => [],
            ]);
        }

        $this->addExistingDeadlines(
            $monthly,
            $startDate,
            $endDate
        );

        $this->addSubscriptionRecurrences(
            $monthly,
            $startDate,
            $endDate
        );

        $monthsData = $monthly
            ->map(function (array $month) {
                $month['total'] = round($month['total'], 2);

                usort($month['items'], function (array $a, array $b) {
                    return strcmp($a['date'], $b['date']);
                });

                return $month;
            })
            ->values();

        $totalForecast = round(
            $monthsData->sum('total'),
            2
        );

        $activeMonths = $monthsData
            ->filter(fn (array $month) => $month['total'] > 0);

        $peakMonth = $activeMonths
            ->sortByDesc('total')
            ->first();

        $suggestedMonthlyReserve = $totalForecast > 0
            ? round($totalForecast / $months, 2)
            : 0.0;

        return [
            'range_start' => $startDate->format('Y-m-d'),
            'range_end' => $endDate->format('Y-m-d'),
            'months' => $monthsData,
            'total_forecast' => $totalForecast,
            'total_items' => $monthsData->sum('count'),
            'peak_month' => $peakMonth,
            'suggested_monthly_reserve' => $suggestedMonthlyReserve,
        ];
    }

    protected function addExistingDeadlines(
        Collection $monthly,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $deadlines = Deadline::query()
            ->with(['subscription', 'vehicle', 'user'])
            ->where('pagato', false)
            ->whereDate('data_scadenza', '>=', $startDate)
            ->whereDate('data_scadenza', '<=', $endDate)
            ->get();

        foreach ($deadlines as $deadline) {
            $date = Carbon::parse($deadline->data_scadenza);
            $key = $date->format('Y-m');

            if (!$monthly->has($key)) {
                continue;
            }

            $item = [
                'source' => 'deadline',
                'deadline_id' => $deadline->id,
                'subscription_id' => $deadline->subscription_id,
                'date' => $date->format('Y-m-d'),
                'title' => $this->deadlineTitle($deadline),
                'type' => $deadline->tipo,
                'amount' => (float) ($deadline->importo ?? 0),
            ];

            $this->addItem($monthly, $key, $item);
        }
    }

    protected function addSubscriptionRecurrences(
        Collection $monthly,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $subscriptions = Subscription::query()
            ->whereNotNull('data_scadenza')
            ->get();

        foreach ($subscriptions as $subscription) {
            $intervalMonths = max(
                1,
                (int) ($subscription->renewal_interval_months ?? 1)
            );

            $nextDate = Carbon::parse($subscription->data_scadenza)
                ->startOfDay();

            while ($nextDate->lt($startDate)) {
                $nextDate->addMonthsNoOverflow($intervalMonths);
            }

            while ($nextDate->lte($endDate)) {
                $key = $nextDate->format('Y-m');

                if ($monthly->has($key)) {
                    $alreadyRepresented = collect(
                        $monthly->get($key)['items']
                    )->contains(function (array $item) use (
                        $subscription,
                        $nextDate
                    ) {
                        return $item['source'] === 'deadline'
                            && (int) ($item['subscription_id'] ?? 0) === (int) $subscription->id
                            && $item['date'] === $nextDate->format('Y-m-d');
                    });

                    if (!$alreadyRepresented) {
                        $this->addItem($monthly, $key, [
                            'source' => 'subscription_forecast',
                            'deadline_id' => null,
                            'subscription_id' => $subscription->id,
                            'date' => $nextDate->format('Y-m-d'),
                            'previous_date' => $nextDate
                                ->copy()
                                ->subMonthsNoOverflow($intervalMonths)
                                ->format('Y-m-d'),
                            'title' => $subscription->nome,
                            'type' => 'spesa_abbonamento',
                            'amount' => (float) ($subscription->importo ?? 0),
                        ]);
                    }
                }

                $nextDate->addMonthsNoOverflow($intervalMonths);
            }
        }
    }

    protected function addItem(
        Collection $monthly,
        string $key,
        array $item
    ): void {
        $month = $monthly->get($key);

        $month['total'] += $item['amount'];
        $month['count']++;
        $month['items'][] = $item;

        $monthly->put($key, $month);
    }

    protected function deadlineTitle(Deadline $deadline): string
    {
        if (
            $deadline->tipo === 'spesa_abbonamento' &&
            $deadline->subscription
        ) {
            return $deadline->subscription->nome;
        }

        if ($deadline->vehicle) {
            return ucfirst(
                str_replace('_', ' ', $deadline->tipo)
            ) . ' — ' . strtoupper(
                $deadline->vehicle->marca
                . ' '
                . $deadline->vehicle->modello
            );
        }

        if ($deadline->user) {
            return ucfirst(
                str_replace('_', ' ', $deadline->tipo)
            ) . ' — ' . $deadline->user->name;
        }

        return ucfirst(
            str_replace('_', ' ', $deadline->tipo)
        );
    }
}