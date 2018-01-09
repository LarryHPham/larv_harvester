<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Url;
use App\Keyword;
use App\KeywordModified;

class KeywordApi extends Controller
{
    /**
     * Retrieves and displays the keywords for a given article model
     * @param  Url  $UrlModel The URL to fetch keywords for
     * @return View           A display of the associated keywords
     */
    private function getArticleKeywords($UrlModel)
    {
        // Get the keywords
        $Keywords = [];
        $UsedKeywords = [];
        foreach ($UrlModel->keywords as $Keyword) {
            if (sizeof($UsedKeywords) < 3 || $Keyword->pivot->weight >= 10) {
                $UsedKeywords[] = $Keyword;
            }

            $Keywords[] = [
                $Keyword->raw,
                $Keyword->pivot->weight,
                route('api.v1.keyword', ['keyword_id' => $Keyword->id]),
            ];
        }

        // Get the modified keywords
        $ModifiedKeywords = [];
        foreach ($UrlModel->keywords_modified as $Keyword) {
            $ModifiedKeywords[] = [
                $Keyword->modifier->raw . ' ' . $Keyword->keyword->raw,
                $Keyword->pivot->weight,
                route('api.v1.keyword_modified', ['keyword_id' => $Keyword->id]),
            ];
        }

        // Get the related articles based on top keywords
        $RelatedArticles = [];
        foreach ($UsedKeywords as $Keyword) {
            // Take top 3 or 10+ weight
            $KeywordArticles = 0;
            foreach ($Keyword->articles as $Article) {
                // Check for current article
                if ($Article->id === $UrlModel->id) {
                    continue;
                }

                // Skip if we have enough articles
                if ($Article->pivot->weight < 10 && $KeywordArticles >= 3) {
                    continue;
                }

                // Build the array item if needed
                if (!isset($RelatedArticles[$Article->id])) {
                    $RelatedArticles[$Article->id] = [
                        'model' => $Article,
                        'weight' => 0,
                    ];
                }

                // Add the weight to the array
                $RelatedArticles[$Article->id]['weight'] += ($Article->pivot->weight * $Keyword->pivot->weight);
            }
        }

        // Sort the related articles
        $RelatedArticles = collect($RelatedArticles)
            ->sortByDesc('weight')
            ->take(10)
            ->map(function ($ArticleItem) {
                return [
                    $ArticleItem['model']->article_url,
                    $ArticleItem['weight'],
                    route('api.v1.article', ['url_id' => $ArticleItem['model']->id]),
                ];
            });

        // Get linked articles
        $ArticlesLinkedTo = $UrlModel
            ->articleLinksTo
            ->map(function ($Article) {
                return [
                    $Article->article_url,
                    route('api.v1.article', ['url_id' => $Article->id]),
                ];
            });

        // Get linked articles
        $ArticlesLinkedFrom = $UrlModel
            ->articleLinkedIn
            ->map(function ($Article) {
                return [
                    $Article->article_url,
                    route('api.v1.article', ['url_id' => $Article->id]),
                ];
            });

        return view('keywords', [
            'title' => 'Article Keywords - ' . $UrlModel->article_url,
            'keywords' => $Keywords,
            'modified_keywords' => $ModifiedKeywords,
            'articles' => $RelatedArticles,
            'articles_to' => $ArticlesLinkedTo,
            'articles_from' => $ArticlesLinkedFrom,
        ]);
    }

    /**
     * Retrieves and displays article keywords by article ID
     * @param  integer $url_id The ID of the article
     * @return View            The view
     */
    public function article($url_id)
    {
        // Get the article
        $UrlModel = Url::find($url_id);

        return $this->getArticleKeywords($UrlModel);
    }

    /**
     * Retrieves and displays article keywords by article URL
     * @param  Request $request The request with associated input
     * @return View             The article keywords
     */
    public function article_url(Request $request)
    {
        // Get the URL to check
        $url = Url::sanitizeUrl($request->input('url'));

        // Get the model
        $UrlModel = Url::findByHash($url);

        return $this->getArticleKeywords($UrlModel);
    }

    /**
     * Retrieves articles associated with the given keyword
     * @param  integer $keyword_id The ID of the keyword to query
     * @return View                The associated articles
     */
    public function keyword($keyword_id)
    {
        // Get the keyword
        $KeywordModel = Keyword::find($keyword_id);

        // Get the articles
        $Articles = [];
        foreach ($KeywordModel->articles as $Article) {
            $Articles[] = [
                $Article->article_url,
                $Article->pivot->weight,
                route('api.v1.article', ['url_id' => $Article->id]),
            ];
        }

        return view('keywords', [
            'title' => $KeywordModel->raw . ' Articles',
            'articles' => $Articles,
        ]);
    }

    /**
     * Retrieves articles associated with the given modified keyword
     * @param  integer $keyword_id The modified keyword ID
     * @return View                The associated articles
     */
    public function keyword_modified($keyword_id)
    {
        // Get the keyword
        $KeywordModel = KeywordModified::find($keyword_id);

        // Get the articles
        $Articles = [];
        foreach ($KeywordModel->articles as $Article) {
            $Articles[] = [
                $Article->article_url,
                $Article->pivot->weight,
                route('api.v1.article', ['url_id' => $Article->id]),
                $Article->article_url,
            ];
        }

        return view('keywords', [
            'title' => $KeywordModel->modifier->raw . ' ' . $KeywordModel->keyword->raw . ' Articles',
            'articles' => $Articles,
        ]);
    }
}
