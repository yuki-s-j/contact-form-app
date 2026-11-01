<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ContactController::class, 'index']);
Route::post('/contacts/confirm', [ContactController::class, 'confirm']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/thanks', [ContactController::class, 'thanks']);

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
});

Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->middleware('auth');
Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->middleware('auth');

Route::post('/admin/tags', [TagController::class, 'store'])->middleware('auth');

Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])->middleware('auth');
Route::put('/admin/tags/{tag}', [TagController::class, 'update'])->middleware('auth');
Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy'])->middleware('auth');
