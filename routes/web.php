<?php

use App\Models\Plan;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\AiResumeController;
use App\Http\Controllers\PlanController;
use App\Models\Template;
use App\Http\Controllers\NotificationController; // إضافة جديدة للإشعارات
use App\Models\User;
use Illuminate\Support\Facades\Hash;


Route::get('/setup-admin', function () {
    // تأكد من تغيير هذه البيانات لبياناتك الخاصة
    $user = User::create([
        'name' => 'Ahmedou Med',
        'email' => 'kmed2498@gmail.com', // ضع بريدك هنا
        'password' => Hash::make('Ahmedounaje72021'), // ضع كلمة مرور قوية هنا
    ]);

    return "Admin created successfully!";
});

Route::get('/', function () {
    $plans = Plan::all();
    $templates = Template::all(); // جلب جميع القوالب (يمكن إضافة where('is_active', true))
    return view('welcome', compact('plans', 'templates'));
});

Route::get('/dashboard', function () {
    $resumes = auth()->user()->resumes()->latest()->get();
    return view('dashboard', compact('resumes'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/cv/{uuid}', [ResumeController::class, 'show'])->name('resume.show')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::put('/cover-letters/{id}', [CoverLetterController::class, 'update'])->name('cover-letters.update');
Route::get('/cover-letters/{id}/combined-download', [CoverLetterController::class, 'combinedDownload'])->name('cover-letters.combined-download');
    // Payment
    Route::get('/payment/checkout/{slug}', [App\Http\Controllers\PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/payment/online/{slug}', [App\Http\Controllers\PaymentController::class, 'processOnlinePayment'])->name('payment.online');
    Route::post('/payment/manual', [App\Http\Controllers\PaymentController::class, 'manualPayment'])->name('payment.manual');
    
    // Templates & Resumes
    Route::get('/templates/choose', [ResumeController::class, 'showTemplates'])->name('templates.choose');
    Route::post('/resumes/start', [ResumeController::class, 'startWithTemplate'])->name('resumes.start');
    Route::get('/cv/{uuid}/edit', [ResumeController::class, 'edit'])->name('resume.edit');
    Route::put('/cv/{uuid}/update', [ResumeController::class, 'update'])->name('resume.update');
    Route::get('/resume/build', [ResumeController::class, 'create'])->name('resume.create');
    Route::post('/resume/store', [ResumeController::class, 'store'])->name('resume.store');
    
    // AI
    Route::post('/ai/generate', [AiGenerationController::class, 'generate'])->name('ai.generate');
    Route::post('/ai/review', [AiGenerationController::class, 'reviewResume'])->name('ai.review');
    Route::post('/api/cv-parse', [AiResumeController::class, 'parseFile'])->name('api.cv.parse');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Plans
    Route::get('/plans/{slug}', [PlanController::class, 'show'])->name('plans.show');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Cover Letters
    Route::get('/cover-letters/create', [CoverLetterController::class, 'create'])->name('cover-letters.create');
    Route::post('/cover-letters', [CoverLetterController::class, 'store'])->name('cover-letters.store');
    Route::get('/cover-letters/{id}', [CoverLetterController::class, 'show'])->name('cover-letters.show');
    Route::get('/cover-letters/{id}/download', [CoverLetterController::class, 'downloadPdf'])->name('cover-letters.download');
});

Route::post('/ai/review-resume', [AiGenerationController::class, 'reviewResume'])->middleware('auth');
Route::get('/cv/{uuid}/download', [ResumeController::class, 'downloadPdf'])->name('resume.download')->middleware('auth');
Route::post('/moosyl/webhook', [App\Http\Controllers\PaymentController::class, 'handleWebhook']);
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en', 'fr'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

require __DIR__.'/auth.php';