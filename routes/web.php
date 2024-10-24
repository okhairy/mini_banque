<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QrCodeController; // Importer le QrCodeController
use App\Http\Controllers\HomeController; // Importer le HomeController pour plus de clarté

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ici, vous pouvez enregistrer les routes web pour votre application. Ces
| routes sont chargées par le RouteServiceProvider et appartiendront toutes
| au groupe middleware "web". Créez quelque chose de génial !
|
*/

// Route vers la page d'accueil (welcome)
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Auth::routes();

// Route pour la page d'accueil affichant les transactions et le solde
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Route pour initier un transfert depuis la page d'accueil
Route::post('/home', [HomeController::class, 'transfer'])->name('home.transfer');

// Route pour générer un QR code pour le numéro de compte
Route::get('/generate-qr/{accountNumber}', [QrCodeController::class, 'generateQrCode'])->name('generate.qr');
