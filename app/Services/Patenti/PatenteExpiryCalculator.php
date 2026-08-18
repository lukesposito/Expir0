<?php

namespace App\Services\Patenti;

use App\Models\User;
use Carbon\Carbon;

class PatenteExpiryCalculator
{
    public function calculate(?int $userId, Carbon|string $dataRinnovoStr, ?string $tipo = null): array
    {
        $user = $userId ? User::find($userId) : null;
        $dataRinnovo = Carbon::parse($dataRinnovoStr)->startOfDay();

        $categorie = collect(explode(',', strtoupper(str_replace(' ', '', $tipo ?? ''))))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $categorieLeggere = ['AM', 'A1', 'A2', 'A', 'B1', 'B', 'BE'];
        $categorieC = ['C1', 'C1E', 'C', 'CE'];
        $categorieD = ['D1', 'D1E', 'D', 'DE'];

        $haD = count(array_intersect($categorie, $categorieD)) > 0;
        $haC = count(array_intersect($categorie, $categorieC)) > 0;
        $haLeggere = empty($categorie) || count(array_intersect($categorie, $categorieLeggere)) > 0;

        if (!$user || !$user->data_nascita) {
            $anni = 10;

            if ($haD || $haC) {
                $anni = 5;
            }

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $dataRinnovo->copy()->addYears($anni)->format('Y-m-d'),
                'messaggio' => 'Calcolo effettuato senza data di nascita: impossibile allineare al compleanno.',
            ];
        }

        $dataNascita = Carbon::parse($user->data_nascita)->startOfDay();
        $eta = $dataNascita->diffInYears($dataRinnovo);

        if ($haD) {
            if ($eta < 70) {
                return [
                    'status' => 'ok_auto',
                    'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, 5)->format('Y-m-d'),
                    'messaggio' => null,
                ];
            }

            if ($eta < 80) {
                return [
                    'status' => 'ok_auto',
                    'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, 3)->format('Y-m-d'),
                    'messaggio' => null,
                ];
            }

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, 2)->format('Y-m-d'),
                'messaggio' => null,
            ];
        }

        if ($haC) {
            if ($eta < 65) {
                return [
                    'status' => 'ok_auto',
                    'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, 5)->format('Y-m-d'),
                    'messaggio' => null,
                ];
            }

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, 2)->format('Y-m-d'),
                'messaggio' => 'Dopo i 65 anni la patente C/CE/C1/C1E richiede verifica in Commissione Medica Locale secondo il caso.',
            ];
        }

        if ($haLeggere) {
            if ($eta < 50) {
                $anni = 10;
            } elseif ($eta < 70) {
                $anni = 5;
            } elseif ($eta < 80) {
                $anni = 3;
            } else {
                $anni = 2;
            }

            return [
                'status' => 'ok_auto',
                'data_scadenza' => $this->alignToBirthday($dataRinnovo, $dataNascita, $anni)->format('Y-m-d'),
                'messaggio' => null,
            ];
        }

        return [
            'status' => 'manuale_richiesto',
            'data_scadenza' => null,
            'messaggio' => 'Categoria patente non riconosciuta automaticamente.',
        ];
    }

    protected function alignToBirthday(Carbon $dataRinnovo, Carbon $dataNascita, int $anni): Carbon
    {
        $base = $dataRinnovo->copy()->addYears($anni - 1);
        $scadenza = $dataNascita->copy()->year($base->year);

        if ($scadenza->lt($base)) {
            $scadenza->addYear();
        }

        return $scadenza->startOfDay();
    }
}