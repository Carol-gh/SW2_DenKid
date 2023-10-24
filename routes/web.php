<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\InfanteController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\PlanAccionController;


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
    return view('auth.login');
});



Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('personal', PersonalController::class);
    Route::resource('infantes', InfanteController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('evaluaciones', EvaluacionController::class);
    Route::post('change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');
    Route::get('/evaluar/{id}', [App\Http\Controllers\EvaluacionController::class, 'evaluar'])->name('evaluar');
    Route::get('/preguntas/{areaId}/{evaluacionId}', [App\Http\Controllers\PreguntasController::class, 'getItem'])->name('preguntas');
    Route::post('/guardar_respuestas/{evaluacionId}', [App\Http\Controllers\PreguntasController::class, 'respuestas'])->name('guardar_respuestas');

     Route::resource('planAccion',PlanAccionController ::class)->names('planAccion');
    Route::get('/PAccion/{id}', [App\Http\Controllers\PlanAccionController::class, 'crear'])->name('PAccion');
    Route::get('/Seguimiento/{id}', [App\Http\Controllers\SeguimientoController::class, 'crear'])->name('seguimiento.crear');
    Route::resource('seguimiento',SeguimientoController ::class)->names('seguimiento');
    Route::post('/process-image', 'App\Http\Controllers\ImageController@processImage')->name('process.image');

});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
