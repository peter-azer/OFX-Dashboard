<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\PhoneContactsController;
use App\Http\Controllers\WhatsAppContactsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('heroes', HeroController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('abouts', AboutController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('works', WorkController::class);
    Route::apiResource('teams', TeamController::class);
    Route::apiResource('phone-contacts', PhoneContactsController::class);
    Route::apiResource('whatsapp-contacts', WhatsAppContactsController::class);
});

Route::post('/admin/login', [LoginController::class, 'login']);

// Phone Contacts
Route::get('/next-contact', [PhoneContactsController::class, 'nextPhoneNumber']);
Route::post('/phone/{phone_contact}/record', [PhoneContactsController::class, 'recordPhoneNumber']);

// WhatsApp Contacts
Route::get('/next-whatsapp-contact', [WhatsAppContactsController::class, 'nextWhatsAppNumber']);
Route::post('/whatsapp/{whatsapp_contact}/record', [WhatsAppContactsController::class, 'recordWhatsAppNumber']);

Route::get('/home', [PageController::class, 'home']);
