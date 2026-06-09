<?php

use App\Http\Controllers\AffectationTacheController;
use App\Http\Controllers\AgenceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BanqueController;
use App\Http\Controllers\CompagnieController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\DossierEmployeeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePdfController;
use App\Http\Controllers\EmployeeWizardController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FacturePdfController;
use App\Http\Controllers\FonctionController;
use App\Http\Controllers\LivretBancaireController;
use App\Http\Controllers\PayementEmployeeController;
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

Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('compagnies', CompagnieController::class)->parameters([
        'compagnies' => 'compagnie',
    ]);

    Route::resource('agences', AgenceController::class)->parameters([
        'agences' => 'agence',
    ]);

    Route::resource('fonctions', FonctionController::class)->parameters([
        'fonctions' => 'fonction',
    ]);

    Route::resource('employees', EmployeeController::class)->parameters([
        'employees' => 'employee',
    ]);

    Route::get('employees/{employee}/pdf', [EmployeePdfController::class, 'fiche'])->name('employees.pdf');

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

    Route::resource('dossiers-employees', DossierEmployeeController::class)->parameters([
        'dossiers-employees' => 'dossierEmployee',
    ]);

    Route::resource('conges', CongeController::class)->parameters([
        'conges' => 'conge',
    ]);

    Route::resource('retenus', RetenuController::class)->parameters([
        'retenus' => 'retenu',
    ]);

    Route::resource('primes', PrimeController::class)->parameters([
        'primes' => 'prime',
    ]);

    Route::resource('payements-employees', PayementEmployeeController::class)->parameters([
        'payements-employees' => 'payementEmployee',
    ]);

    Route::resource('projects', ProjectController::class)->parameters([
        'projects' => 'project',
    ]);

    Route::get('projects/{project}/manage', [ProjectController::class, 'manage'])->name('projects.manage');
    Route::post('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status');
    Route::post('projects/{project}/taches', [ProjectController::class, 'storeTache'])->name('projects.taches.store');

    Route::resource('taches-projects', TacheProjectController::class)->parameters([
        'taches-projects' => 'tacheProject',
    ]);

    Route::put('taches-projects/{tacheProject}/status', [TacheProjectController::class, 'updateStatusInline'])->name('taches-projects.status-inline');
    Route::post('taches-projects/{tacheProject}/affectations', [AffectationTacheController::class, 'storeInline'])->name('taches-projects.affectations.store');

    Route::resource('affectation-taches', AffectationTacheController::class)->parameters([
        'affectation-taches' => 'affectationTache',
    ]);

    Route::delete('affectation-taches/{affectationTache}/inline', [AffectationTacheController::class, 'destroyInline'])->name('affectation-taches.destroy-inline');

    Route::resource('contacts', ContactController::class)->parameters([
        'contacts' => 'contact',
    ]);

    Route::resource('banques', BanqueController::class)->parameters([
        'banques' => 'banque',
    ]);

    Route::resource('livrets-bancaires', LivretBancaireController::class)->parameters([
        'livrets-bancaires' => 'livretBancaire',
    ]);

    Route::resource('factures', FactureController::class)->parameters([
        'factures' => 'facture',
    ]);

    Route::get('factures/{facture}/manage', [FactureController::class, 'manage'])->name('factures.manage');
    Route::post('factures/{facture}/statut', [FactureController::class, 'updateStatut'])->name('factures.statut');
    Route::post('factures/{facture}/items', [FactureController::class, 'storeItem'])->name('factures.items.store');
    Route::delete('items-facture/{itemFacture}', [FactureController::class, 'destroyItem'])->name('factures.items.destroy');
    Route::get('factures/{facture}/pdf', [FacturePdfController::class, 'download'])->name('factures.pdf');

    Route::resource('users', UserController::class)->parameters([
        'users' => 'user',
    ]);
});
