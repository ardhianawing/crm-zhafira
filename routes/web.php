<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\IconController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\LeadAssignmentController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhatsappTemplateController;
use App\Http\Controllers\Marketing\DashboardController as MarketingDashboardController;
use App\Http\Controllers\Marketing\LeadController as MarketingLeadController;
use App\Http\Controllers\Marketing\FollowUpController;
use App\Http\Controllers\Marketing\NewsController as MarketingNewsController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

// Privacy Policy (required by Meta)
Route::get('/privacy-policy', function () {
    return response(
        '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">'
        . '<meta property="og:title" content="Privacy Policy - Zhafira Villa">'
        . '<meta property="og:description" content="Kebijakan Privasi Zhafira Villa">'
        . '<meta property="og:type" content="website">'
        . '<meta property="og:url" content="https://crm.zhafiravila.com/privacy-policy">'
        . '<title>Privacy Policy - Zhafira Villa</title></head>'
        . '<body style="font-family:sans-serif;max-width:700px;margin:40px auto;padding:0 20px">'
        . '<h1>Privacy Policy</h1>'
        . '<p><strong>Zhafira Villa</strong></p>'
        . '<p>Zhafira Villa menghormati privasi Anda. Data yang kami kumpulkan melalui WhatsApp (nama dan nomor telepon) hanya digunakan untuk keperluan komunikasi dan layanan kami.</p>'
        . '<p>Kami tidak akan membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.</p>'
        . '<h2>Data yang Dikumpulkan</h2>'
        . '<ul><li>Nama</li><li>Nomor WhatsApp</li><li>Pesan yang dikirim</li></ul>'
        . '<h2>Penggunaan Data</h2>'
        . '<p>Data digunakan semata-mata untuk merespons pertanyaan Anda dan memberikan layanan terbaik.</p>'
        . '<h2>Kontak</h2>'
        . '<p>Email: zhafira.office@gmail.com</p>'
        . '<p><small>Terakhir diperbarui: Februari 2026</small></p>'
        . '</body></html>',
        200
    )->header('Content-Type', 'text/html');
})->name('privacy-policy');

// Redirect root
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin() 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('marketing.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/icons/icon-{dimensions}.png', [IconController::class, 'generate']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Push Notification routes
    Route::get('/push/key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
});

// Admin routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Fitur Update Status Cepat
        Route::patch('/leads/{lead}/update-status', [AdminLeadController::class, 'updateStatus'])->name('leads.update-status');
        
        Route::resource('leads', AdminLeadController::class);
        Route::get('/leads/{lead}/history', [AdminLeadController::class, 'history'])->name('leads.history');
        Route::get('/assignment', [LeadAssignmentController::class, 'index'])->name('assignment.index');
        Route::post('/assignment/{lead}/assign', [LeadAssignmentController::class, 'assignSingle'])->name('assignment.single');
        Route::post('/assignment/bulk', [LeadAssignmentController::class, 'assignBulk'])->name('assignment.bulk');
        Route::post('/assignment/transfer', [LeadAssignmentController::class, 'transferBulk'])->name('assignment.transfer');
        Route::post('/assignment/delete', [LeadAssignmentController::class, 'deleteBulk'])->name('assignment.delete');
        Route::post('/assignment/toggle-rotator', [LeadAssignmentController::class, 'toggleRotator'])->name('assignment.toggle-rotator');
        Route::resource('news', AdminNewsController::class);
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('whatsapp-templates', WhatsappTemplateController::class);
        Route::patch('/whatsapp-templates/{whatsapp_template}/toggle-status', [WhatsappTemplateController::class, 'toggleStatus'])->name('whatsapp-templates.toggle-status');
    });

// Marketing routes
Route::prefix('marketing')
    ->name('marketing.')
    ->middleware(['auth', 'active', 'marketing'])
    ->group(function () {
        Route::get('/dashboard', [MarketingDashboardController::class, 'index'])->name('dashboard');
        Route::get('/leads', [MarketingLeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/create', [MarketingLeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [MarketingLeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}', [MarketingLeadController::class, 'show'])->name('leads.show');
        Route::put('/leads/{lead}', [MarketingLeadController::class, 'update'])->name('leads.update');
        Route::get('/tasks/today', [FollowUpController::class, 'todaysTasks'])->name('tasks.today');
        Route::post('/tasks/{lead}/complete', [FollowUpController::class, 'complete'])->name('tasks.complete');
        Route::get('/news', [MarketingNewsController::class, 'index'])->name('news.index');
        Route::get('/tools', function () {
            return view('marketing.tools');
        })->name('tools');
    });