<?php

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\PhoneContactsController;
use App\Http\Controllers\WhatsAppContactsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\EmailsController;
use App\Http\Controllers\FormSubmitionController;
use App\Http\Controllers\VisitorsController;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [LoginController::class, 'login']);

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
    Route::apiResource('users', UserController::class);
    Route::apiResource('phone-contacts', PhoneContactsController::class);
    Route::apiResource('whatsapp-contacts', WhatsAppContactsController::class);
    Route::apiResource('emails', EmailsController::class);
    // Custom email routes
    Route::put('emails/{email}/toggle-status', [EmailsController::class, 'toggleStatus']);
    Route::put('emails/{email}/set-main', [EmailsController::class, 'setAsMain']);
    Route::get('analytics-overview', [AnalyticsController::class, 'overview']);
    Route::get('roles', [UserController::class, 'roles']);
    
    //view geoIp analytics and trafic
    Route::get('visits/total', [VisitorsController::class, 'total']);
    Route::get('visits/unique-today', [VisitorsController::class, 'uniqueToday']);
    Route::get('visits/by-country', [VisitorsController::class, 'byCountry']);
    Route::get('visits/top-pages', [VisitorsController::class, 'topPages']);
    Route::get('visits/per-day', [VisitorsController::class, 'visitsPerDay']);
});

Route::get('phone-records', [PhoneContactsController::class, 'showPhoneRecords']);
Route::get('whatsapp-records', [WhatsAppContactsController::class, 'showWhatsAppRecords']);
Route::apiResource('form-submissions', FormSubmitionController::class);
Route::get('/home', [PageController::class, 'home']); 
Route::get('/blogs', [BlogsController::class, 'index']);
Route::get('/blogs/{post}', [BlogsController::class, 'show']);
Route::get('/next-contact', [PhoneContactsController::class, 'nextPhoneNumber']);
Route::post('/phone/{phone_contact}/record', [PhoneContactsController::class, 'recordPhoneNumber']);
Route::post('submit-form', [FormSubmitionController::class, 'store']);
Route::get('/next-whatsapp-contact', [WhatsAppContactsController::class, 'nextWhatsAppNumber']);
Route::post('/whatsapp/{whatsapp_contact}/record', [WhatsAppContactsController::class, 'recordWhatsAppNumber']);
Route::get('/service/{slug}', [ServiceController::class, 'servicePage']);
Route::get('/work/{slug}', [WorkController::class, 'workPage']);

