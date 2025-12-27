<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Article;



class BlogScraperController extends Controller
{
    private $baseUrl = 'https://beyondchats.com/blogs';
    private $articlesNeeded = 5;

    /**
     * Scrape and save last 5 articles to database (with full content)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scrapeAndSaveArticles(Request $request)
    {
        try {
            // Get scraped articles
            $scrapedData = $this->scrapeLastFiveArticles();
            
            if (!$scrapedData['success']) {
                return response()->json($scrapedData, 500);
            }

            $savedArticles = [];
            $stats = [
                'new' => 0,
                'updated' => 0,
                'skipped' => 0
            ];

            DB::beginTransaction();

            foreach ($scrapedData['articles'] as $articleData) {
                // Fetch full content for this article
                $fullContent = null;
                if (!empty($articleData['url'])) {
                    $fullContent = $this->scrapeArticleContent($articleData['url']);
                }

                // Check if article already exists
                $article = Article::where('url', $articleData['url'])->first();
                
                if ($article) {
                    // Update existing article
                    $article->update([
                        'title' => $articleData['title'],
                        'excerpt' => $articleData['excerpt'],
                        'image' => $articleData['image'],
                        'image_alt' => $articleData['image_alt'],
                        'author_name' => $articleData['author']['name'],
                        'author_url' => $articleData['author']['url'],
                        'date' => $articleData['date'],
                        'categories' => $articleData['categories'],
                        'full_content' => $fullContent, // Save full content
                    ]);
                    $stats['updated']++;
                } else {
                    // Create new article
                    $article = Article::create([
                        'title' => $articleData['title'],
                        'url' => $articleData['url'],
                        'excerpt' => $articleData['excerpt'],
                        'image' => $articleData['image'],
                        'image_alt' => $articleData['image_alt'],
                        'author_name' => $articleData['author']['name'],
                        'author_url' => $articleData['author']['url'],
                        'date' => $articleData['date'],
                        'categories' => $articleData['categories'],
                        'full_content' => $fullContent, // Save full content
                    ]);
                    $stats['new']++;
                }

                $savedArticles[] = $article;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Articles saved successfully with full content',
                'stats' => $stats,
                'total_articles' => count($savedArticles),
                'articles' => $savedArticles
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get articles from database
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArticles(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $category = $request->get('category'); // Optional filter by category

            $query = Article::latest();

            // Filter by category if provided
            if ($category) {
                $query->byCategory($category);
            }

            $articles = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'articles' => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    } 


    // below is the remaining crud methods

    /**
     * Format organized content into readable text
     * 
     * @param array $organizedContent
     * @return string
     */
    private function formatOrganizedContent($organizedContent)
    {
        $output = [];
        
        foreach ($organizedContent as $item) {
            switch ($item['type']) {
                case 'heading':
                    $prefix = str_repeat('#', $item['level']);
                    $output[] = "\n{$prefix} {$item['text']}\n";
                    break;
                    
                case 'paragraph':
                    $output[] = $item['text'] . "\n";
                    break;
                    
                case 'unordered_list':
                    foreach ($item['items'] as $listItem) {
                        $output[] = "• {$listItem}";
                    }
                    $output[] = "";
                    break;
                    
                case 'ordered_list':
                    foreach ($item['items'] as $index => $listItem) {
                        $num = $index + 1;
                        $output[] = "{$num}. {$listItem}";
                    }
                    $output[] = "";
                    break;
                    
                case 'quote':
                    $output[] = "> {$item['text']}\n";
                    break;
            }
        }
        
        $formatted = implode("\n", $output);
        
        // Clean up excessive newlines
        $formatted = preg_replace('/\n{3,}/', "\n\n", $formatted);
        
        return trim($formatted);
    }


    /**
     * Get a single article by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArticle($id)
    {
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'article' => $article
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an article by ID
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateArticle(Request $request, $id)
    {
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'url' => 'sometimes|string|url|unique:articles,url,' . $id,
                'excerpt' => 'sometimes|string',
                'image' => 'sometimes|string|url',
                'image_alt' => 'sometimes|string',
                'author_name' => 'sometimes|string',
                'author_url' => 'sometimes|string|url',
                'date' => 'sometimes|string',
                'categories' => 'sometimes|array',
                'categories.*.name' => 'required_with:categories|string',
                'categories.*.url' => 'required_with:categories|string',
                'full_content' => 'sometimes|string',

                'is_optimized' => 'sometimes|boolean',
                'reference_articles' => 'sometimes|array',
                'reference_articles.*.title' => 'required_with:reference_articles|string',
                'reference_articles.*.url' => 'required_with:reference_articles|string|url',
            ]);

            // Update article
            $article->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully',
                'article' => $article->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an article by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteArticle($id)
    {
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found'
                ], 404);
            }

            // Store article data before deleting for the response
            $deletedArticle = [
                'id' => $article->id,
                'title' => $article->title,
                'url' => $article->url
            ];

            // Delete the article
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully',
                'deleted_article' => $deletedArticle
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple articles by IDs
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMultipleArticles(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer|exists:articles,id'
            ]);

            $deletedCount = Article::whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} article(s)",
                'deleted_count' => $deletedCount
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Scrape last 5 articles (helper method)
     */
    private function scrapeLastFiveArticles()
    {
        try {
            $articles = [];
            $currentPage = $this->getLastPageNumber();
            
            while (count($articles) < $this->articlesNeeded && $currentPage > 0) {
                $pageArticles = $this->scrapePageArticles($currentPage);
                
                foreach ($pageArticles as $article) {
                    if (count($articles) < $this->articlesNeeded) {
                        $articles[] = $article;
                    } else {
                        break 2;
                    }
                }
                
                $currentPage--;
            }

            return [
                'success' => true,
                'total_articles' => count($articles),
                'articles' => $articles
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get last page number
     */
    private function getLastPageNumber()
    {
        $response = Http::timeout(30)->get($this->baseUrl);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch the blog page');
        }

        $html = $response->body();
        $crawler = new Crawler($html);
        
        $lastPageNumber = 1;
        $crawler->filter('.page-numbers')->each(function (Crawler $node) use (&$lastPageNumber) {
            $text = trim($node->text());
            if (is_numeric($text)) {
                $pageNum = (int)$text;
                if ($pageNum > $lastPageNumber) {
                    $lastPageNumber = $pageNum;
                }
            }
        });

        return $lastPageNumber;
    }

    /**
     * Scrape articles from page
     */
    private function scrapePageArticles($pageNumber)
    {
        $url = $pageNumber === 1 
            ? $this->baseUrl 
            : $this->baseUrl . '/page/' . $pageNumber . '/';

        $response = Http::timeout(30)->get($url);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page $pageNumber");
        }

        $html = $response->body();
        $crawler = new Crawler($html);
        
        $articles = [];
        
        $crawler->filter('article')->each(function (Crawler $article) use (&$articles) {
            try {
                $titleNode = $article->filter('h2 a');
                $title = $titleNode->count() > 0 ? trim($titleNode->text()) : 'No title';
                $url = $titleNode->count() > 0 ? $titleNode->attr('href') : '';

                $imageNode = $article->filter('img');
                $image = $imageNode->count() > 0 ? $imageNode->attr('src') : '';
                $imageAlt = $imageNode->count() > 0 ? $imageNode->attr('alt') : '';

                $excerptNode = $article->filter('p');
                $excerpt = $excerptNode->count() > 0 ? trim($excerptNode->text()) : '';

                $authorNode = $article->filter('.author a');
                $author = $authorNode->count() > 0 ? trim($authorNode->text()) : '';
                $authorUrl = $authorNode->count() > 0 ? $authorNode->attr('href') : '';

                $dateNode = $article->filter('time');
                if ($dateNode->count() === 0) {
                    $dateNode = $article->filter('.posted-on');
                }
                $date = $dateNode->count() > 0 ? trim($dateNode->text()) : '';

                $categories = [];
                $article->filter('.tag a, .category a')->each(function (Crawler $tag) use (&$categories) {
                    $categories[] = [
                        'name' => trim($tag->text()),
                        'url' => $tag->attr('href')
                    ];
                });

                $articles[] = [
                    'title' => $title,
                    'url' => $url,
                    'excerpt' => $excerpt,
                    'image' => $image,
                    'image_alt' => $imageAlt,
                    'author' => [
                        'name' => $author,
                        'url' => $authorUrl
                    ],
                    'date' => $date,
                    'categories' => $categories
                ];

            } catch (\Exception $e) {
                Log::warning('Error parsing article: ' . $e->getMessage());
            }
        });

        return array_reverse($articles);
    }

    /**
     * Scrape full content from an article page
     * 
     * @param string $url
     * @return string|null
     */


    private function scrapeArticleContent($url)
    {
        try {
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                Log::warning("Failed to fetch article content from: $url");
                return 'Content not available';
            }

            $html = $response->body();
            $crawler = new Crawler($html);
            
            // Try to find the main article content container
            $selectors = [
                '.entry-content',
                '.post-content',
                '.article-content',
                'article .content',
                '.single-post-content'
            ];
            
            $contentNode = null;
            foreach ($selectors as $selector) {
                try {
                    $node = $crawler->filter($selector);
                    if ($node->count() > 0) {
                        $contentNode = $node->first();
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (!$contentNode || $contentNode->count() === 0) {
                Log::warning("Could not find content container for: $url");
                return 'Content structure not recognized';
            }

            // Extract organized content
            $organizedContent = [];
            
            // Extract headings and paragraphs in order
            $contentNode->filter('h1, h2, h3, h4, h5, h6, p, ul, ol, blockquote')->each(function (Crawler $node) use (&$organizedContent) {
                $tagName = $node->nodeName();
                $text = trim($node->text());
                
                // Skip empty elements
                if (empty($text) || strlen($text) < 10) {
                    return;
                }
                
                // Skip social sharing, navigation, and promotional text
                $skipPatterns = [
                    'share',
                    'follow us',
                    'subscribe',
                    'click here',
                    'read more',
                    'comment',
                    'applause',
                    'facebook',
                    'twitter',
                    'linkedin',
                    'whatsapp',
                    'pinterest'
                ];
                
                $lowerText = strtolower($text);
                foreach ($skipPatterns as $pattern) {
                    if (stripos($lowerText, $pattern) !== false && strlen($text) < 100) {
                        return;
                    }
                }
                
                // Format based on tag type
                if (in_array($tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                    // Headings
                    $level = (int)substr($tagName, 1);
                    $organizedContent[] = [
                        'type' => 'heading',
                        'level' => $level,
                        'text' => $text
                    ];
                } elseif ($tagName === 'p') {
                    // Paragraphs
                    $organizedContent[] = [
                        'type' => 'paragraph',
                        'text' => $text
                    ];
                } elseif (in_array($tagName, ['ul', 'ol'])) {
                    // Lists
                    $listItems = [];
                    $node->filter('li')->each(function (Crawler $li) use (&$listItems) {
                        $itemText = trim($li->text());
                        if (!empty($itemText)) {
                            $listItems[] = $itemText;
                        }
                    });
                    
                    if (!empty($listItems)) {
                        $organizedContent[] = [
                            'type' => $tagName === 'ul' ? 'unordered_list' : 'ordered_list',
                            'items' => $listItems
                        ];
                    }
                } elseif ($tagName === 'blockquote') {
                    // Blockquotes
                    $organizedContent[] = [
                        'type' => 'quote',
                        'text' => $text
                    ];
                }
            });
            
            if (empty($organizedContent)) {
                return 'No readable content found';
            }
            
            // Convert organized content to clean text format
            $formattedContent = $this->formatOrganizedContent($organizedContent);
            
            return $formattedContent;

        } catch (\Exception $e) {
            Log::error("Error scraping article content from $url: " . $e->getMessage());
            return 'Error fetching content: ' . $e->getMessage();
        }
    }


    

}




 

/**
 * Routes (Add to routes/web.php or routes/api.php):
 * 
 * Route::get('/scrape-blog', [BlogScraperController::class, 'scrapeLastFiveArticles']);
 * Route::get('/scrape-blog-with-content', [BlogScraperController::class, 'scrapeLastFiveArticlesWithContent']);
 * 
 * Usage:
 * GET http://yourapp.com/scrape-blog
 * GET http://yourapp.com/scrape-blog-with-content
 * 
 * Response Format:
 * {
 *     "success": true,
 *     "total_articles": 5,
 *     "articles": [
 *         {
 *             "title": "Article Title",
 *             "url": "https://beyondchats.com/blogs/article-slug/",
 *             "excerpt": "Article description...",
 *             "image": "https://beyondchats.com/wp-content/uploads/...",
 *             "image_alt": "Image description",
 *             "author": {
 *                 "name": "Author Name",
 *                 "url": "https://beyondchats.com/author/..."
 *             },
 *             "date": "November 28, 2025",
 *             "categories": [
 *                 {
 *                     "name": "AI Chatbots",
 *                     "url": "https://beyondchats.com/blogs/tag/ai-chatbots/"
 *                 }
 *             ]
 *         }
 *     ]
 * }
 */
