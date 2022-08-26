<?php

use App\Http\Controllers\RaceConditionController;
use Illuminate\Support\Facades\Route;

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

/*
    * Note:
        - Im writing this code using laravel 8.83.23 & php 7.4.19

    run url race-condition-x at the same time
    - in chrome you can CTRL + Left click (on tab) === (block page), then CTRL + R === (reload page)
    - now you can see on your DB (database) simple race condition situation

    * if you want to add new process race condition you can add manually on the controller!.
        You just copy paste like existing code.
*/
Route::get('/race-condition-1', [RaceConditionController::class, 'process_1']);
Route::get('/race-condition-2', [RaceConditionController::class, 'process_2']);
Route::get('/race-condition-3', [RaceConditionController::class, 'process_3']);
Route::get('/race-condition-4', [RaceConditionController::class, 'process_4']);
Route::get('/race-condition-5', [RaceConditionController::class, 'process_5']);
Route::get('/race-condition-6', [RaceConditionController::class, 'process_6']);

/*
    Before i found a code to handle race condition, im used this url to get a new idea
*/
Route::get('/missing-value', [RaceConditionController::class, 'missing_value']);
Route::get('/double-value', [RaceConditionController::class, 'double_value']);
Route::get('/process-count', [RaceConditionController::class, 'process_count']);

/*
    You can open this url to get normal process
*/
Route::get('/mask-process', [RaceConditionController::class, 'mask_process']); // normal process
