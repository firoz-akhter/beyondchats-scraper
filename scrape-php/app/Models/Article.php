<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Article extends Model
// {
//     //
// }




namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'excerpt',
        'image',
        'image_alt',
        'author_name',
        'author_url',
        'date',
        'published_at',
        'categories',
        'full_content',
        'is_optimized',          // NEW
        'reference_articles',     // NEW
        'optimized_at',          // NEW 
    ];

    protected $casts = [
        'published_at' => 'date',
        'categories' => 'array', // Automatically cast JSON to array
        // 'is_optimized' => 'boolean',      // NEW
        'reference_articles' => 'array',  // NEW
        'optimized_at' => 'datetime', 
    ];

    /**
     * Parse date string to Carbon instance
     */
    // public function setDateAttribute($value)
    // {
    //     $this->attributes['date'] = $value;
        
    //     // Try to parse the date string to a proper date
    //     try {
    //         $this->attributes['published_at'] = Carbon::parse($value);
    //     } catch (\Exception $e) {
    //         $this->attributes['published_at'] = null;
    //     }
    // }

    /**
     * Scope: Get latest articles
     */
    // public function scopeLatest($query)
    // {
    //     return $query->orderBy('published_at', 'desc')
    //                 ->orderBy('created_at', 'desc');
    // }

    /**
     * Scope: Filter by category name
     */
    // public function scopeByCategory($query, $categoryName)
    // {
    //     return $query->whereJsonContains('categories', [['name' => $categoryName]]);
    // }

    /**
     * Scope: Get only optimized articles
     */
    // public function scopeOptimized($query)
    // {
    //     return $query->where('is_optimized', true);
    // }

    /**
     * Scope: Get unoptimized articles
     */
    // public function scopeUnoptimized($query)
    // {
    //     return $query->where('is_optimized', false);
    // }

    /**
     * Get all unique category names from database
     */
    // public static function getAllCategories()
    // {
    //     $articles = self::whereNotNull('categories')->get();
    //     $allCategories = [];

    //     foreach ($articles as $article) {
    //         if (is_array($article->categories)) {
    //             foreach ($article->categories as $category) {
    //                 if (isset($category['name'])) {
    //                     $allCategories[$category['name']] = $category;
    //                 }
    //             }
    //         }
    //     }

    //     return array_values($allCategories);
    // }


    // public function markAsOptimized($referenceArticles, $model)
    // {
    //     $this->update([
    //         'is_optimized' => true,
    //         'reference_articles' => $referenceArticles,
    //         'optimized_at' => now(),
    //         'optimization_model' => $model,
    //         'optimization_version' => $this->optimization_version + 1
    //     ]);
    // }
}
