<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/auth', [ApiController::class, 'auth']);
    Route::post('/logout', [ApiController::class, 'logout']);

    Route::group(
        [
            "prefix" => "todos",
            "as" => "todos."
        ], function() {
            Route::get('/', [ApiController::class, 'getTodos']);
            Route::post('/', [ApiController::class, 'createTodo']);
            Route::put('/{id}', [ApiController::class, 'updateTodo']);
            Route::delete('/{id}', [ApiController::class, 'deleteTodo']);
        }
    );
});

Route::post('/register', [ApiController::class, 'register']);
Route::post('/login', [ApiController::class, 'login']);