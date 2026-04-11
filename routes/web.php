<?php
use App\Models\Plan;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\AiGenerationController;
use App\Models\Resume;
use App\Http\Controllers\AiResumeController;
use App\Http\Controllers\PlanController; // 👈 1. أضف هذا السطر هنا

Route::get('/', function () {
    $plans = Plan::all();
    return view('welcome', compact('plans'));
});

Route::get('/dashboard', function () {
    $resumes = auth()->user()->resumes()->latest()->get();
    return view('dashboard', compact('resumes'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/cv/{uuid}', [ResumeController::class, 'show'])->name('resume.show');

Route::middleware('auth')->group(function () {
    Route::get('/templates/choose', [ResumeController::class, 'showTemplates'])->name('templates.choose');
    Route::post('/resumes/start', [ResumeController::class, 'startWithTemplate'])->name('resumes.start');
Route::get('/cv/{uuid}/edit', [ResumeController::class, 'edit'])->name('resume.edit');
Route::put('/cv/{uuid}/update', [ResumeController::class, 'update'])->name('resume.update');
    Route::post('/ai/generate', [AiGenerationController::class, 'generate'])->name('ai.generate');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/resume/build', [ResumeController::class, 'create'])->name('resume.create');
    Route::post('/resume/store', [ResumeController::class, 'store'])->name('resume.store');
    
    // 👇 2. أضف مسار الباقات هنا 👇
    Route::get('/plans/{slug}', [PlanController::class, 'show'])->name('plans.show');
});

Route::post('/ai/review', [AiGenerationController::class, 'reviewResume'])->name('ai.review');
Route::post('/ai/review-resume', [AiGenerationController::class, 'reviewResume']);
Route::get('/cv/{uuid}/download', [ResumeController::class, 'downloadPdf'])->name('resume.download');
Route::post('/api/cv-parse', [AiResumeController::class, 'parseFile'])->name('api.cv.parse');

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en', 'fr'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

require __DIR__.'/auth.php';