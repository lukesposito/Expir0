<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DeadlineController;
use App\Http\Controllers\PatenteController;
use App\Http\Controllers\RevisioneController;
use App\Http\Controllers\BolloController;
use App\Http\Controllers\AssicurazioneController;
use App\Http\Controllers\VehicleDetailController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CartaIdentitaController;
use App\Http\Controllers\TesserinoSanitarioController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\ActionProposalController;
use App\Http\Controllers\SavingsPlanController;
use App\Http\Middleware\Check2FA;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Deadline;
use Carbon\Carbon;


use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

// Registra le rotte Passkey in modo standard
WebAuthnRoutes::register();

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/check-email-passkey', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    
    return response()->json([
        'exists' => !!$user,
        // Usiamo la stessa logica "antiproiettile" del controller per interrogare il DB
        'has_passkey' => $user ? DB::table('webauthn_credentials')
            ->where('authenticatable_id', $user->getAuthIdentifier())
            ->where('authenticatable_type', get_class($user))
            ->exists() : false
    ]);
})->name('check.passkey');

// IL GRUPPO PRINCIPALE: tutte le rotte che devono essere protette da login, verifica email e 2FA
Route::middleware(['auth', 'verified', Check2FA::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/documents', [DashboardController::class, 'documents'])->name('dashboard.documents');
    Route::get('/dashboard/review-inbox', [DashboardController::class, 'reviewInbox'])->name('dashboard.review-inbox');
    Route::patch('/dashboard/review-inbox/{extraction}/status', [DashboardController::class, 'updateReviewStatus'])->name('dashboard.review-inbox.update');
    Route::get('/vehicles/{vehicle}/detail', [VehicleDetailController::class, 'show'])->name('vehicles.detail');
    
    // Pluralizzazione in Inglese
    // Perché? Perché Laravel usa una libreria di pluralizzazione inglese (Str::singular()) che conosce le regole inglesi.
    // - deadlines → deadline (rimuove la 's')
    // - vehicles → vehicle (rimuove la 's')
    // Quindi per deadlines, Laravel automaticamente usa {deadline} come parametro, stessa cosa per vehicles.
    // Per patenti e revisioni, dobbiamo specificarlo manualmente, perchè italiano non segue le regole inglesi.
    // La libreria di pluralizzazione di Laravel non conosce l'italiano. Non sa che revisioni → revisione.
    
    // ROUTE RESOURCE
    Route::resource('vehicles', VehicleController::class);
    Route::resource('patenti', PatenteController::class)->parameters([
        'patenti' => 'patente'
    ]);
    Route::resource('deadlines', DeadlineController::class);
    Route::patch('/deadlines/{deadline}/toggle-pagato', [DeadlineController::class, 'togglePagato'])->name('deadlines.togglePagato');
    Route::resource('revisioni', RevisioneController::class)->parameters([
        'revisioni' => 'revisione'
    ]);
    Route::resource('bolli', BolloController::class)->parameters([
        'bolli' => 'bollo'
    ]);
    Route::resource('assicurazioni', AssicurazioneController::class)->parameters([
        'assicurazioni' => 'assicurazione'
    ]);
    Route::resource('carte-identita', CartaIdentitaController::class)->parameters([
        'carte-identita' => 'carta_identita'
    ]);
    Route::resource('tesserini-sanitari', TesserinoSanitarioController::class)->parameters([
        'tesserini-sanitari' => 'tesserino_sanitario'
    ]);
    Route::resource('subscriptions', SubscriptionController::class);

    // ROUTE DOCUMENTI
    Route::post('/documents/store', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // ROTTE PROFILO
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // ROTTE MFA (Autenticazione a Due Fattori)
    Route::post('/2fa/generate', [TwoFactorController::class, 'generateSecret'])->name('2fa.generate');
    Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::get('/2fa/verify', [TwoFactorController::class, 'index'])->name('2fa.index');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    // ROTTA PER ELIMINARE LE PASSKEY (WebAuthn)
    Route::delete('/profile/passkey/{id}', [ProfileController::class, 'destroyPasskey'])->name('profile.passkey.destroy');
    // ROTTE ADMIN (Gestione Utenti)
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('admin.users.toggle');
    // ROTTE PER LA GESTIONE DELLE NOTIFICHE PUSH
    Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');

    // LA ROTTA DELL'ASSISTENTE AI (Chatbot)
    // Protetta da un middleware di rate limiting per evitare abusi e per garantire che l'AI venga usata in modo responsabile. Il limite è definito in AppServiceProvider.
    // Protetta dal throttle "ai-agent" (Max 5 al giorno)
    Route::post('/ai-assistant/ask', [AiAgentController::class, 'ask'])->middleware('throttle:ai-agent')->name('ai.ask');

    // Nuova rotta per l'analisi avanzata (con prompt più complesso e dati reali)
    Route::post('/ai-assistant/analyze', [AiAgentController::class, 'analyze'])->name('ai.analyze');

    Route::get('/azioni', [ActionProposalController::class, 'index'])->name('action-proposals.index');
    Route::patch('/azioni/{actionProposal}/rifiuta', [ActionProposalController::class, 'reject'])->name('action-proposals.reject');
    Route::post('/azioni/{actionProposal}/conferma', [ActionProposalController::class, 'confirm'])->name('action-proposals.confirm');

    Route::post('/piani-accantonamento', [SavingsPlanController::class, 'store'])->name('savings-plans.store');
    Route::delete('/piani-accantonamento/{savingsPlan}', [SavingsPlanController::class, 'destroy'])->name('savings-plans.destroy');

    Route::post('/piani-accantonamento/{savingsPlan}/contributo', [SavingsPlanController::class, 'addContribution'])->name('savings-plans.contribution');
});






// ROTTA PER LA SINCRONIZZAZIONE DEL CALENDARIO (iCal Feed)
// Mettila FUORI dal middleware 'auth' così Google/Apple possono leggerla!
Route::get('/calendario/feed/{token}', function ($token) {
    // 1. Controllo di sicurezza: scegliamo una password segreta per il link
    $segreto = env('CALENDAR_FEED_TOKEN');
    
    if ($token !== $segreto) {
        abort(403, 'Accesso negato: Token calendario non valido.');
    }

    // 2. Recupero tutte le scadenze
    $deadlines = Deadline::with(['vehicle', 'user'])->get();

    // 3. Costruisco il file iCal secondo gli standard internazionali
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//RMA AUTO//Gestione Scadenze//IT\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";
    $ical .= "X-WR-CALNAME:Scadenze RMA AUTO\r\n"; // Il nome che apparirà sul tuo telefono!
    $ical .= "X-WR-TIMEZONE:Europe/Rome\r\n";

    foreach ($deadlines as $d) {
        $start = Carbon::parse($d->data_scadenza);
        $end = $start->copy()->addDay(); // Per iCal, gli eventi "tutto il giorno" finiscono il giorno dopo

        $title = strtoupper($d->tipo) . ' ' . ($d->vehicle ? $d->vehicle->targa : ($d->user ? $d->user->name : ''));
        $desc = "Importo: € " . number_format($d->importo ?? 0, 2, ',', '.') . "\\nStato: " . ($d->pagato ? "Pagato" : "Da pagare") . "\\nNote: " . str_replace(["\r", "\n"], ['', "\\n"], $d->note ?? '');
        $uid = md5('scadenza_' . $d->id . $d->tipo) . "@rmaauto.it";

        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:{$uid}\r\n";
        $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical .= "DTSTART;VALUE=DATE:" . $start->format('Ymd') . "\r\n";
        $ical .= "DTEND;VALUE=DATE:" . $end->format('Ymd') . "\r\n";
        $ical .= "SUMMARY:{$title}\r\n";
        $ical .= "DESCRIPTION:{$desc}\r\n";
        $ical .= "END:VEVENT\r\n";
    }

    $ical .= "END:VCALENDAR";

    // 4. Restituisco il file al browser/telefono
    return response($ical)
        ->header('Content-Type', 'text/calendar; charset=utf-8')
        ->header('Content-Disposition', 'attachment; filename="scadenze-rma.ics"');
})->name('calendario.feed');

require __DIR__.'/auth.php';