<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;

Route::get("/", function () {
    return inertia("DangerZone");
})->name("home");

Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"]);
Route::get("/register", [AuthController::class, "showRegister"])->name("register");
Route::post("/register", [AuthController::class, "register"]);
Route::post("/logout", [AuthController::class, "logout"])->name("logout");

// Waiting approval route
Route::get("/waiting-approval", function (\Illuminate\Http\Request $request) {
    if ($request->user() && ($request->user()->status === 'approved' || $request->user()->is_admin)) {
        return redirect('/');
    }
    return inertia("Auth/Waiting");
})->middleware('auth')->name('waiting');

// Locale switching route
Route::post("/locale/{locale}", function ($locale) {
    if (in_array($locale, ["en", "ar"])) {
        session(["locale" => $locale]);
    }
    return redirect()->back();
})->name("locale.switch");

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

