<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Url;
use GuzzleHttp\Client as GuzzleClient;

class ArticleApi extends Controller
{
    /**
     * This function returns the keywords to search for for a given article
     * @param  Url    $UrlModel The article to get keywords for
     * @return string           A CSV of the keywords to search for
     */
    private function getArticleKeywords(Url $UrlModel)
    {
        // Get the list of the keywords
        $Keywords = $UrlModel->keywords;

        // Get the average weight
        $KeywordsAverage =
            ceil($Keywords
                ->sum(function ($word) {
                    return $word->pivot->weight;
                }) / $Keywords->count());

        // Get the keywords that are above average
        $Keywords = $Keywords
            ->filter(function ($Keyword) use ($KeywordsAverage) {
                return $Keyword->pivot->weight > $KeywordsAverage;
            });

        // Get the modified keywords
        $ModifiedKeywords = $UrlModel->keywords_modified;

        // Get the average weight
        $ModifiedKeywordsAverage =
            ceil($ModifiedKeywords
                ->sum(function ($word) {
                    return $word->pivot->weight;
                }) / $ModifiedKeywords->count());

        // Get the keywords that are above average
        $ModifiedKeywords = $ModifiedKeywords
            ->filter(function ($Keyword) use ($ModifiedKeywordsAverage) {
                return $Keyword->pivot->weight > $ModifiedKeywordsAverage;
            });

        // Return a CSV of the keywords
        return $ModifiedKeywords
            ->map(function ($Keyword) {
                // Turn modified keywords into a string
                return $Keyword->modifier->lemma . ' ' . $Keyword->keyword->lemma;
            })
            ->merge(
                $Keywords
                    ->map(function ($Keyword) {
                        // Turn regular keywords into a string
                        return $Keyword->lemma;
                    })
                )
            ->implode(',');
    }

    /**
     * This function calls the API for related articles and returns the results
     * @param  $Keywords   A CSV of keywords to send to the API
     * @param  array  $Parameters The parameters to pass into the API
     * @return array              The response from the API
     */
    private function getRelatedArticles($Keywords, Url $UrlModel, $Parameters)
    {
        // Add the keywords to the query
        if ($Keywords !== '' && $Keywords !== null) {
            $Parameters['keywords'] = $Keywords;
        }

        // @TODO: Add the ability to make the current article not return

        // Build the API request
        // https://github.com/passit/Article-Search#search-api
        $RequestUrl = env('ES_FQDN') . '/api/search?' . http_build_query($Parameters);

        // Make an instance of the client
        $Client = new GuzzleClient();

        // Make the request to the API
        $ApiResponse = $Client->request('GET', $RequestUrl);

        // Parse the response
        $ApiResponse = json_decode($ApiResponse->getBody(), true);

        // Add the keywords and article id
        $ApiResponse['keywords'] = $Keywords;
        $ApiResponse['article'] = $UrlModel->id;
        $ApiResponse['url'] = $RequestUrl;

        // Return the $ApiResponse
        return $ApiResponse;
    }

    /**
     * This handles API requests to the related articles API
     * @param  Request  $Request The request parameters
     * @param  integer  $url_id  (optional) The ID of the URL to parse
     * @return Response          The related articles
     */
    public function relatedArticle(Request $Request, $url_id = null)
    {
        // Get the model
        $UrlModel = Url::find($url_id);

        // Get the keywords
        $Keywords = $this->getArticleKeywords($UrlModel);

        // Get the API response
        $Response = $this->getRelatedArticles($Keywords, $UrlModel, $Request->all());

        return response()->json($Response);
    }
}
