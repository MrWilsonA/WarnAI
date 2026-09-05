<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;

Route::get('/', [WorkspaceController::class, 'index']);

Route::prefix('api')->group(function () {
    Route::get('/health', [WorkspaceController::class, 'health']);
    Route::get('/analytics', [WorkspaceController::class, 'analytics']);
    Route::post('/normalize', [WorkspaceController::class, 'normalize']);
    Route::post('/search', [WorkspaceController::class, 'search']);
    Route::post('/context/assemble', [WorkspaceController::class, 'assembleContext']);
    Route::post('/infer', [WorkspaceController::class, 'infer']);
    Route::get('/skeleton', [WorkspaceController::class, 'skeleton']);
    Route::post('/workspace/reset', [WorkspaceController::class, 'resetWorkspace']);
    Route::get('/export/bundle', [WorkspaceController::class, 'exportBundle']);
    Route::get('/export/zip', [WorkspaceController::class, 'exportZip']);
    Route::get('/file/markdown', [WorkspaceController::class, 'fileMarkdown']);
});
