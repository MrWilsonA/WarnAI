<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;
Route::get('/',[WorkspaceController::class,'index']);
Route::get('/api/health',[WorkspaceController::class,'health']);
Route::get('/api/analytics',[WorkspaceController::class,'analytics']);
Route::post('/api/normalize',[WorkspaceController::class,'normalize']);
Route::post('/api/search',[WorkspaceController::class,'search']);
