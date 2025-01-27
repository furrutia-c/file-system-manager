<?php

use Illuminate\Support\Facades\Route;
use Furrutiac\FileSystemManager\Http\Controllers\FileSystemController;

// ...existing code...

Route::middleware(['web'])->group(function () {
  Route::get('/ftp-manager/{path?}', [FileSystemController::class, 'index'])->where('path', '.*')->name('ftp.index');
  Route::post('/ftp-manager/upload/{path?}', [FileSystemController::class, 'upload'])->where('path', '.*')->name('ftp.upload');
  Route::delete('/ftp-manager/delete/{path}', [FileSystemController::class, 'delete'])->where('path', '.*')->name('ftp.delete');
  Route::post('/ftp-manager/create-folder/{path?}', [FileSystemController::class, 'createFolder'])->where('path', '.*')->name('ftp.createFolder');
  Route::post('/ftp-manager/move', [FileSystemController::class, 'move'])->name('ftp.move');
});
