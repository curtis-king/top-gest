<?php

use App\Http\Controllers\AffectationTacheController;
use App\Http\Controllers\CategorieDocumentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPdfController;
use App\Http\Controllers\AgenceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BanqueController;
use App\Http\Controllers\CategorieDepenseController;
use App\Http\Controllers\CompagnieController;
use App\Http\Controllers\ComptabiliteRapportController;
use App\Http\Controllers\ComptabiliteRapportPdfController;
use App\Http\Controllers\CompteComptableController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DossierEmployeeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePdfController;
use App\Http\Controllers\EmployeeWizardController;
use App\Http\Controllers\EcritureComptableController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FacturePdfController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\JournalComptableController;
use App\Http\Controllers\LivretBancaireController;
use App\Http\Controllers\PayementEmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrimeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RetenuController;
use App\Http\Controllers\TacheProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {

    // ── Administration ────────────────────────────────────────────────
    Route::middleware('permission:administration.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->parameters(['users' => 'user']);
    });

    // ── RH / Personnel ───────────────────────────────────────────────
    Route::middleware('permission:rh.view')->group(function () {
        Route::resource('compagnies', CompagnieController::class)->parameters(['compagnies' => 'compagnie']);
        Route::resource('agences', AgenceController::class)->parameters(['agences' => 'agence']);
        Route::resource('fonctions', FonctionController::class)->parameters(['fonctions' => 'fonction']);
        Route::resource('employees', EmployeeController::class)->parameters(['employees' => 'employee']);

        Route::get('employees/{employee}/pdf', [EmployeePdfController::class, 'preview'])->name('employees.pdf');
        Route::get('employees/{employee}/pdf/stream', [EmployeePdfController::class, 'stream'])->name('employees.pdf.stream');
        Route::get('employees/{employee}/pdf/download', [EmployeePdfController::class, 'download'])->name('employees.pdf.download');
        Route::get('employees/{employee}/contrat/stream', [EmployeePdfController::class, 'streamContrat'])->name('employees.contrat.stream');
        Route::get('employees/{employee}/contrat/download', [EmployeePdfController::class, 'downloadContrat'])->name('employees.contrat.download');

        Route::prefix('employees/wizard')->name('employees.wizard.')->group(function () {
            Route::get('/', [EmployeeWizardController::class, 'create'])->name('create');
            Route::post('/', [EmployeeWizardController::class, 'store'])->name('store');
            Route::get('{employee}', [EmployeeWizardController::class, 'edit'])->name('edit');
            Route::post('{employee}/step1', [EmployeeWizardController::class, 'updateStep1'])->name('save-step1');
            Route::post('{employee}/step2', [EmployeeWizardController::class, 'saveStep2'])->name('save-step2');
            Route::post('{employee}/conges', [EmployeeWizardController::class, 'addConge'])->name('add-conge');
            Route::delete('{employee}/conges/{conge}', [EmployeeWizardController::class, 'deleteConge'])->name('delete-conge');
            Route::post('{employee}/primes', [EmployeeWizardController::class, 'addPrime'])->name('add-prime');
            Route::delete('{employee}/primes/{prime}', [EmployeeWizardController::class, 'deletePrime'])->name('delete-prime');
            Route::post('{employee}/retenus', [EmployeeWizardController::class, 'addRetenu'])->name('add-retenu');
            Route::delete('{employee}/retenus/{retenu}', [EmployeeWizardController::class, 'deleteRetenu'])->name('delete-retenu');
            Route::post('{employee}/payements', [EmployeeWizardController::class, 'addPayement'])->name('add-payement');
            Route::delete('{employee}/payements/{payement}', [EmployeeWizardController::class, 'deletePayement'])->name('delete-payement');
        });

        Route::resource('dossiers-employees', DossierEmployeeController::class)->parameters(['dossiers-employees' => 'dossierEmployee']);
        Route::resource('conges', CongeController::class)->parameters(['conges' => 'conge']);
        Route::resource('retenus', RetenuController::class)->parameters(['retenus' => 'retenu']);
        Route::resource('primes', PrimeController::class)->parameters(['primes' => 'prime']);
        Route::resource('payements-employees', PayementEmployeeController::class)->parameters(['payements-employees' => 'payementEmployee']);
    });

    // ── Projets ───────────────────────────────────────────────────────
    Route::middleware('permission:projets.view')->group(function () {
        Route::resource('projects', ProjectController::class)->parameters(['projects' => 'project']);
        Route::get('projects/{project}/manage', [ProjectController::class, 'manage'])->name('projects.manage');
        Route::post('projects/{project}/taches', [ProjectController::class, 'storeTache'])->name('projects.taches.store');

        Route::resource('taches-projects', TacheProjectController::class)->parameters(['taches-projects' => 'tacheProject']);
        Route::post('taches-projects/{tacheProject}/affectations', [AffectationTacheController::class, 'storeInline'])->name('taches-projects.affectations.store');

        Route::resource('affectation-taches', AffectationTacheController::class)->parameters(['affectation-taches' => 'affectationTache']);
        Route::delete('affectation-taches/{affectationTache}/inline', [AffectationTacheController::class, 'destroyInline'])->name('affectation-taches.destroy-inline');
    });

    Route::post('projects/{project}/status', [ProjectController::class, 'updateStatus'])
        ->name('projects.status')
        ->middleware('permission:projets.status');

    Route::put('taches-projects/{tacheProject}/status', [TacheProjectController::class, 'updateStatusInline'])
        ->name('taches-projects.status-inline')
        ->middleware('permission:projets.status');

    // ── Finance ───────────────────────────────────────────────────────
    Route::middleware('permission:finance.view')->group(function () {
        Route::get('contacts/search', [ContactController::class, 'search'])->name('contacts.search');
        Route::resource('contacts', ContactController::class)->parameters(['contacts' => 'contact']);

        Route::resource('banques', BanqueController::class)->parameters(['banques' => 'banque']);
        Route::resource('livrets-bancaires', LivretBancaireController::class)->parameters(['livrets-bancaires' => 'livretBancaire']);

        Route::get('factures/search', [FactureController::class, 'search'])->name('factures.search');
        Route::resource('factures', FactureController::class)->parameters(['factures' => 'facture']);
        Route::get('factures/{facture}/manage', [FactureController::class, 'manage'])->name('factures.manage');
        Route::post('factures/{facture}/items', [FactureController::class, 'storeItem'])->name('factures.items.store');
        Route::delete('items-facture/{itemFacture}', [FactureController::class, 'destroyItem'])->name('factures.items.destroy');
        Route::get('factures/{facture}/pdf', [FacturePdfController::class, 'preview'])->name('factures.pdf');
        Route::get('factures/{facture}/pdf/stream', [FacturePdfController::class, 'stream'])->name('factures.pdf.stream');
        Route::get('factures/{facture}/pdf/download', [FacturePdfController::class, 'download'])->name('factures.pdf.download');

        // ── Comptabilité ──────────────────────────────────────────────
        Route::resource('comptes-comptables', CompteComptableController::class)
            ->parameters(['comptes-comptables' => 'compteComptable'])
            ->except(['show']);
        Route::resource('journaux-comptables', JournalComptableController::class)
            ->parameters(['journaux-comptables' => 'journalComptable'])
            ->except(['show']);
        Route::resource('ecritures-comptables', EcritureComptableController::class)
            ->parameters(['ecritures-comptables' => 'ecritureComptable']);
        Route::resource('categories-depenses', CategorieDepenseController::class)
            ->parameters(['categories-depenses' => 'categorieDepense'])
            ->except(['show']);
        Route::resource('depenses', DepenseController::class);

        Route::get('comptabilite/grand-livre', [ComptabiliteRapportController::class, 'grandLivre'])->name('comptabilite.grand-livre');
        Route::get('comptabilite/balance', [ComptabiliteRapportController::class, 'balance'])->name('comptabilite.balance');
        Route::get('comptabilite/bilan', [ComptabiliteRapportController::class, 'bilan'])->name('comptabilite.bilan');
        Route::get('comptabilite/resultat', [ComptabiliteRapportController::class, 'resultat'])->name('comptabilite.resultat');

        Route::get('comptabilite/balance/pdf', [ComptabiliteRapportPdfController::class, 'balance'])->name('comptabilite.balance.pdf');
        Route::get('comptabilite/bilan/pdf', [ComptabiliteRapportPdfController::class, 'bilan'])->name('comptabilite.bilan.pdf');
        Route::get('comptabilite/resultat/pdf', [ComptabiliteRapportPdfController::class, 'resultat'])->name('comptabilite.resultat.pdf');
    });

    Route::post('factures/{facture}/statut', [FactureController::class, 'updateStatut'])
        ->name('factures.statut')
        ->middleware('permission:finance.factures.valider');

    Route::post('ecritures-comptables/{ecritureComptable}/valider', [EcritureComptableController::class, 'valider'])
        ->name('ecritures-comptables.valider')
        ->middleware('permission:finance.ecritures.valider');

    Route::patch('depenses/{depense}/statut', [DepenseController::class, 'updateStatut'])
        ->name('depenses.statut')
        ->middleware('permission:finance.depenses.valider');

    // ── Stock ─────────────────────────────────────────────────────────
    Route::middleware('permission:stock.view')->group(function () {
        Route::resource('categories-produits', CategorieProduitController::class)->parameters(['categories-produits' => 'categorieProduit']);
        Route::resource('depots', DepotController::class)->parameters(['depots' => 'depot']);
        Route::resource('produits', ProduitController::class)->parameters(['produits' => 'produit']);

        Route::get('stocks/dashboard', [StockController::class, 'dashboard'])->name('stocks.dashboard');
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/pdf/etat', [StockPdfController::class, 'etatStock'])->name('stocks.pdf.etat');

        Route::get('mouvements-stocks', [MouvementStockController::class, 'index'])->name('mouvements-stocks.index');
        Route::get('mouvements-stocks/pdf/rapport', [StockPdfController::class, 'rapportMouvements'])->name('mouvements-stocks.pdf.rapport');
        Route::get('mouvements-stocks/{mouvement}/pdf', [StockPdfController::class, 'bonMouvement'])->name('mouvements-stocks.pdf');
    });

    Route::middleware('permission:stock.create')->group(function () {
        Route::get('mouvements-stocks/create', [MouvementStockController::class, 'create'])->name('mouvements-stocks.create');
        Route::post('mouvements-stocks', [MouvementStockController::class, 'store'])->name('mouvements-stocks.store');
    });

    // ── Archives ──────────────────────────────────────────────────────
    Route::middleware('permission:archives.view')->group(function () {
        Route::resource('categories-documents', CategorieDocumentController::class)->parameters(['categories-documents' => 'categoriesDocument']);
        Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::resource('documents', DocumentController::class)->parameters(['documents' => 'document'])->except(['edit', 'update']);
    });
});
