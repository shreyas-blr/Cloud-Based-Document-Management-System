<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;



Route::redirect('/', '/documents');

Route::get('/index', [DocumentController::class , 'index'])->name('dashboard');

Route::get('/documents/search', [DocumentController::class, 'searchView'])
    ->name('documents.search');

Route::get('/documents/searchHandle', [DocumentController::class, 'search'])
    ->name('documents.searchHandle');

Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
Route::get('/documents/{document}/summarize', [DocumentController::class, 'summarize'])->name('documents.summarize');
Route::resource('documents', DocumentController::class);
