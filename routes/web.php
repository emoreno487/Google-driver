<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});
Route::get('google/login',[GoogleDriveController::class,'googleLogin'])->name('google.login');
Route::get('google-drive/file-upload',[GoogleDriveController::class,'googleDriveFilePpload'])->name('google.drive.file.upload');
Route::get('google-driver/file-list',[GoogleDriveController::class,'googleDriverFileList'])->name('google.driver.file.list');