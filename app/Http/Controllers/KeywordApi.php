<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Url;
use App\Keyword;
use App\KeywordModified;

class KeywordApi extends Controller
{
    public function article($url_id)
    {
        // Get the article
        $UrlModel = Url::find($url_id);

        // Get the keywords
        $Keywords = [];
        foreach ($UrlModel->keywords as $Keyword) {
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

        return view('keywords', [
            'title' => 'Article Keywords - ' . $UrlModel->article_url,
            'data' => [
                [
                    'title' => 'Keywords',
                    'data' => $Keywords,
                ],
                [
                    'title' => 'Compound Keywords',
                    'data' => $ModifiedKeywords,
                ],
            ],
        ]);
    }

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
                $Article->article_url,
            ];
        }

        return view('keywords', [
            'title' => $KeywordModel->raw . ' Articles',
            'data' => [
                [
                    'title' => 'Articles',
                    'data' => $Articles,
                ],
            ],
        ]);
    }

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
            'data' => [
                [
                    'title' => 'Articles',
                    'data' => $Articles,
                ],
            ],
        ]);
    }
}
