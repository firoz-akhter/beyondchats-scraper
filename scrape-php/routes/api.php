<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogScraperController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API working'
    ]);
});



// ------------------------- below is our main route -----------------------
// Scrape and save to database
Route::get('/scrape-and-save', [BlogScraperController::class, 'scrapeAndSaveArticles']);
// Get articles from database
Route::get('/articles', [BlogScraperController::class, 'getArticles']); 
// get single article by id
Route::get('/articles/{id}', [BlogScraperController::class, 'getArticle']);  
Route::put('/updateArticle/{id}', [BlogScraperController::class, 'updateArticle']);    
Route::delete('/deleteArticle/{id}', [BlogScraperController::class, 'deleteArticle']); 
// method to delete multiple articles at once
Route::post('/delete-multiple-articles', [BlogScraperController::class, 'deleteMultipleArticles']);
