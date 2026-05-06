<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1alpha/models/gemini-3-flash-preview:generateContent';
    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function ask($prompt)
    {
        if (empty($this->apiKey)) {
            return "Errore: API Key di Gemini mancante nel file .env.";
        }

        try {
            // Chiamata HTTP nativa di Laravel alle API di Google
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Errore nella formattazione della risposta.";
            } else {
                $status = $response->status();
                
                if ($status == 503) {
                    return "In questo momento i miei server sono un po' affollati e sto facendo fatica a concentrarmi! 😅 Riprova tra un paio di minuti.";
                }
                
                if ($status == 429) {
                    return "Ho superato il limite di letture veloci per oggi! Riprova più tardi.";
                }

                // Per altri errori generici
                \Log::error('Errore Gemini API: ' . $response->body());
                return "Mmh, c'è un piccolo problema di connessione ai miei circuiti. Riprova tra poco!";
            }

            Log::error('Errore Gemini API: ' . $response->body());
            return "Errore di connessione con l'Intelligenza Artificiale. Controlla i log.";
        } catch (\Exception $e) {
            Log::error('Eccezione Gemini API: ' . $e->getMessage());
            return "Ops! Ho perso la connessione con la centrale. Verifica la tua rete e riprova.";
        }
    }
}