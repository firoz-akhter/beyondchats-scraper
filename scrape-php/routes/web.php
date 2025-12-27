<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;


// ------------------------- mostly have dummy routes in this route file -------------------------

Route::get('/', function () {
    return view('welcome');
});

// testing working branch working or not


Route::get('/custom', function () {
    return view('custom');
});

// Route::redirect("/home", "/custom");


Route::get('/about/{name}', function ($name) {
    echo $name;
    return view('custom');
});

Route::get("/users", [UserController::class, 'users']);
// Route::get("/aboutUser", [UserController::class, 'aboutUser']);
// Route::get("/user/{name}", [UserController::class, 'getUserName']);

// students route
Route::get("/students", [StudentController::class, 'getStudents']); 

// routes have been moved to api.php








// we will add our routes here
