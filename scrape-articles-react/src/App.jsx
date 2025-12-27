import React, { useState, useEffect } from "react";
import {
  Search,
  Filter,
  Calendar,
  Tag,
  ExternalLink,
  RefreshCw,
  Sparkles,
  TrendingUp,
  Clock,
  User,
} from "lucide-react";

// Configuration
const API_BASE_URL = "http://localhost:8000/api";

const App = () => {
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedArticle, setSelectedArticle] = useState(null);
  const [filter, setFilter] = useState("all"); // all, optimized, original
  const [searchQuery, setSearchQuery] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Fetch articles from API
  const fetchArticles = async (page = 1) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch(
        `${API_BASE_URL}/articles?per_page=10&page=${page}`
      );
      const data = await response.json();

      if (data.success) {
        setArticles(data.articles.data);
        setCurrentPage(data.articles.current_page);
        setTotalPages(data.articles.last_page);
      } else {
        throw new Error("Failed to fetch articles");
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchArticles();
  }, []);

  // Filter articles
  const filteredArticles = articles.filter((article) => {
    const matchesSearch =
      article.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      article.excerpt?.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesFilter =
      filter === "all" ||
      (filter === "optimized" && article.is_optimized) ||
      (filter === "original" && !article.is_optimized);

    return matchesSearch && matchesFilter;
  });

  // Format date
  const formatDate = (dateString) => {
    if (!dateString) return "Unknown";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-2">
                <Sparkles className="text-purple-600" size={32} />
                Article Dashboard
              </h1>
              <p className="text-gray-600 mt-1">
                Browse and manage your optimized articles
              </p>
            </div>
            <button
              onClick={() => fetchArticles(currentPage)}
              className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <RefreshCw size={18} />
              Refresh
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Search and Filter Bar */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
          <div className="flex flex-col lg:flex-row gap-4">
            {/* Search */}
            <div className="flex-1 relative">
              <Search
                className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                size={20}
              />
              <input
                type="text"
                placeholder="Search articles..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              />
            </div>

            {/* Filter Buttons */}
            <div className="flex gap-2">
              <button
                onClick={() => setFilter("all")}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  filter === "all"
                    ? "bg-blue-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                }`}
              >
                All ({articles.length})
              </button>
              <button
                onClick={() => setFilter("optimized")}
                className={`px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 ${
                  filter === "optimized"
                    ? "bg-green-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                }`}
              >
                <TrendingUp size={18} />
                Optimized ({articles.filter((a) => a.is_optimized).length})
              </button>
              <button
                onClick={() => setFilter("original")}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  filter === "original"
                    ? "bg-orange-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                }`}
              >
                Original ({articles.filter((a) => !a.is_optimized).length})
              </button>
            </div>
          </div>
        </div>

        {/* Loading State */}
        {loading && (
          <div className="flex justify-center items-center py-20">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          </div>
        )}

        {/* Error State */}
        {error && (
          <div className="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <p className="text-red-800 font-medium">Error: {error}</p>
            <button
              onClick={() => fetchArticles(currentPage)}
              className="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Try Again
            </button>
          </div>
        )}

        {/* Articles Grid */}
        {!loading && !error && (
          <>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              {filteredArticles.map((article) => (
                <ArticleCard
                  key={article.id}
                  article={article}
                  onClick={() => setSelectedArticle(article)}
                  formatDate={formatDate}
                />
              ))}
            </div>

            {filteredArticles.length === 0 && (
              <div className="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-200">
                <Search className="mx-auto text-gray-400 mb-4" size={48} />
                <h3 className="text-xl font-semibold text-gray-700 mb-2">
                  No articles found
                </h3>
                <p className="text-gray-500">
                  Try adjusting your search or filter
                </p>
              </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex justify-center gap-2 mt-8">
                <button
                  onClick={() => fetchArticles(currentPage - 1)}
                  disabled={currentPage === 1}
                  className="px-4 py-2 bg-white border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <span className="px-4 py-2 bg-blue-600 text-white rounded-lg">
                  {currentPage} / {totalPages}
                </span>
                <button
                  onClick={() => fetchArticles(currentPage + 1)}
                  disabled={currentPage === totalPages}
                  className="px-4 py-2 bg-white border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
      </main>

      {/* Article Detail Modal */}
      {selectedArticle && (
        <ArticleModal
          article={selectedArticle}
          onClose={() => setSelectedArticle(null)}
          formatDate={formatDate}
        />
      )}
    </div>
  );
};

// Article Card Component
const ArticleCard = ({ article, onClick, formatDate }) => {
  return (
    <div
      onClick={onClick}
      className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all cursor-pointer group"
    >
      {/* Image */}
      {article.image && (
        <div className="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
          <img
            src={article.image}
            alt={article.image_alt || article.title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          {article.is_optimized && (
            <div className="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
              <Sparkles size={14} />
              Optimized
            </div>
          )}
        </div>
      )}

      <div className="p-5">
        {/* Title */}
        <h2 className="text-xl font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
          {article.title}
        </h2>

        {/* Excerpt */}
        <p className="text-gray-600 text-sm mb-4 line-clamp-3">
          {article.excerpt || "No excerpt available"}
        </p>

        {/* Metadata */}
        <div className="flex flex-wrap gap-3 text-sm text-gray-500 mb-4">
          {article.date && (
            <div className="flex items-center gap-1">
              <Calendar size={16} />
              <span>{article.date}</span>
            </div>
          )}
          {article.author_name && (
            <div className="flex items-center gap-1">
              <User size={16} />
              <span>{article.author_name}</span>
            </div>
          )}
        </div>

        {/* Categories */}
        {article.categories && article.categories.length > 0 && (
          <div className="flex flex-wrap gap-2 mb-4">
            {article.categories.slice(0, 3).map((cat, idx) => (
              <span
                key={idx}
                className="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium flex items-center gap-1"
              >
                <Tag size={12} />
                {cat.name}
              </span>
            ))}
            {article.categories.length > 3 && (
              <span className="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-xs">
                +{article.categories.length - 3} more
              </span>
            )}
          </div>
        )}

        {/* Optimization Info */}
        {article.is_optimized && (
          <div className="pt-4 border-t border-gray-100">
            <div className="flex items-center justify-between text-xs text-gray-500">
              <div className="flex items-center gap-1">
                <Clock size={14} />
                <span>Optimized {formatDate(article.optimized_at)}</span>
              </div>
              {article.optimization_version > 1 && (
                <span className="bg-purple-100 text-purple-700 px-2 py-0.5 rounded">
                  v{article.optimization_version}
                </span>
              )}
            </div>
            {article.optimization_model && (
              <div className="mt-2 text-xs text-gray-400">
                Model: {article.optimization_model}
              </div>
            )}
          </div>
        )}

        {/* View Details Button */}
        <button className="mt-4 w-full py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
          View Full Article
          <ExternalLink size={16} />
        </button>
      </div>
    </div>
  );
};

// Article Detail Modal
const ArticleModal = ({ article, onClose, formatDate }) => {
  return (
    <div
      className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      onClick={onClose}
    >
      <div
        className="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="bg-linear-to-r from-blue-600 to-purple-600 text-white p-6">
          <div className="flex justify-between items-start mb-4">
            <h2 className="text-2xl font-bold pr-8">{article.title}</h2>
            <button
              onClick={onClose}
              className="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors"
            >
              ✕
            </button>
          </div>

          {article.is_optimized && (
            <div className="inline-flex items-center gap-2 bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">
              <Sparkles size={16} />
              AI-Optimized Article
            </div>
          )}
        </div>

        {/* Modal Body */}
        <div className="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
          {/* Image */}
          {article.image && (
            <img
              src={article.image}
              alt={article.image_alt || article.title}
              className="w-full h-64 object-cover rounded-xl mb-6"
            />
          )}

          {/* Metadata Grid */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
            <div>
              <p className="text-xs text-gray-500 mb-1">Published</p>
              <p className="font-medium text-sm">{article.date || "Unknown"}</p>
            </div>
            {article.author_name && (
              <div>
                <p className="text-xs text-gray-500 mb-1">Author</p>
                <p className="font-medium text-sm">{article.author_name}</p>
              </div>
            )}
            {article.is_optimized && (
              <>
                <div>
                  <p className="text-xs text-gray-500 mb-1">Optimized</p>
                  <p className="font-medium text-sm">
                    {formatDate(article.optimized_at)}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-gray-500 mb-1">Version</p>
                  <p className="font-medium text-sm">
                    v{article.optimization_version || 1}
                  </p>
                </div>
              </>
            )}
          </div>

          {/* Categories */}
          {article.categories && article.categories.length > 0 && (
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-700 mb-2">
                Categories
              </h3>
              <div className="flex flex-wrap gap-2">
                {article.categories.map((cat, idx) => (
                  <span
                    key={idx}
                    className="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"
                  >
                    {cat.name}
                  </span>
                ))}
              </div>
            </div>
          )}

          {/* Content */}
          <div className="prose max-w-none mb-6">
            <h3 className="text-lg font-semibold text-gray-800 mb-3">
              Content Preview
            </h3>
            <div className="text-gray-700 whitespace-pre-wrap">
              {article.full_content
                ? article.full_content.substring(0, 1000) +
                  (article.full_content.length > 1000 ? "..." : "")
                : article.excerpt || "No content available"}
            </div>
          </div>

          {/* Reference Articles */}
          {article.reference_articles &&
            article.reference_articles.length > 0 && (
              <div className="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <h3 className="text-sm font-semibold text-green-800 mb-3 flex items-center gap-2">
                  <TrendingUp size={18} />
                  Reference Articles Used for Optimization
                </h3>
                <div className="space-y-2">
                  {article.reference_articles.map((ref, idx) => (
                    <a
                      key={idx}
                      href={ref.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-start gap-2 p-3 bg-white rounded-lg hover:bg-green-100 transition-colors group"
                    >
                      <span className="font-semibold text-green-700">
                        {idx + 1}.
                      </span>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium text-gray-900 group-hover:text-green-700 transition-colors truncate">
                          {ref.title}
                        </p>
                        <p className="text-xs text-gray-500 truncate">
                          {ref.url}
                        </p>
                      </div>
                      <ExternalLink
                        size={16}
                        className="text-gray-400 group-hover:text-green-600 flex-shrink-0"
                      />
                    </a>
                  ))}
                </div>
              </div>
            )}

          {/* Original URL */}
          {article.url && (
            <div className="mt-6">
              <a
                href={article.url}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                View Original Article
                <ExternalLink size={18} />
              </a>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default App;
