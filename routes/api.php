<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppController;

Route::post('/insertBiometriAttendence',[AppController::class, 'insertBiometriAttendence'])->name('insertBiometriAttendence');
Route::post('/syncAttendance', [AppController::class, 'syncAttendance'])->name('syncAttendance');
