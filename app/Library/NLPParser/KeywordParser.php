<?php

namespace App\Library\NLPParser;

use App\Keyword;
use App\KeywordModified;
use GuzzleHttp\Client as GuzzleClient;

class KeywordParser
{
    /**
     * This GuzzleHttp Client
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * The URL to curl for the results
     * @var String
     */
    private $parser_url = env('STANFORD_URL', 'http://localhost:9000/') . '?properties={"annotators":"tokenize,ssplit,pos,lemma,depparse"}&pipelineLanguage=en';

    /**
     * This function loads the guzzle client
     */
    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    /**
     * This function parses the keywords from the body and title and saves them
     * in the database.
     * @param  App\Url $UrlModel The URL that the data is from
     * @param  String  $Body     The body text (no HTML tags)
     * @param  String  $Title    The article title
     */
    public function parse($UrlModel, $Body, $Title = null)
    {
        $response = $this->client->request('POST', $this->parser_url, [
            'body' => $Body,
        ]);

        $Sentences = json_decode($response->getBody(), true);

        if ($Sentences === null) {
            print 'FATAL ERROR' . "\n";
            return;
        }

        // Build out the keyword array
        $Keywords = [];

        // Save frequencies
        $TotalKeywords = 0;
        $TotalModifiedKeywords = 0;

        // Loop over the Sentances
        foreach ($Sentences['sentences'] as $Sentence) {
            $SentenceKeywords = [];
            $SentenceKeywordIndexes = [];

            // Get the proper nouns
            foreach ($Sentence['tokens'] as $WordIndex => $Word) {
                // Filter for only proper nouns
                if (strpos($Word['pos'], 'NNP') === 0) {
                    // Add to the indexes
                    $SentenceKeywordIndexes[] = $Word['index'];

                    // Make an array for the dependencies
                    $Word['deps'] = [];

                    // Add the word to the array
                    $SentenceKeywords[] = $Word;
                }
            }

            // Get the supporting words
            foreach ($Sentence['enhancedPlusPlusDependencies'] as $Dependency) {
                // Skip non-modifying and non-compound relationships
                if (
                    strpos($Dependency['dep'], 'mod') === false &&
                    strpos($Dependency['dep'], 'compound') === false
                ) {
                    continue;
                }

                // Find the word being used (if it is a proper noun)
                // Use index - 1 because the index starts at 1
                $ParentIndex = array_search($Dependency['governor'], $SentenceKeywordIndexes);

                // Save the modifier if needed
                if ($ParentIndex !== false) {
                    $SentenceKeywords[$ParentIndex]['deps'][] = $Dependency['dependent'];
                }
            }

            // Clean up and save the keywords
            foreach ($SentenceKeywords as $Keyword) {
                // Make the array if needed
                if (!isset($Keywords[$Keyword['lemma']])) {
                    $Keywords[$Keyword['lemma']] = [
                        'lemma' => $Keyword['lemma'],
                        'raw' => $Keyword['word'],
                        'freq' => 0,
                        'modifiers' => [],
                    ];
                }

                // Increment the frequency
                $Keywords[$Keyword['lemma']]['freq']++;
                $TotalKeywords++;

                // Loop over the modifiers
                foreach ($Keyword['deps'] as $Modifier) {
                    // Get the token object
                    $ModifierToken = array_values(array_filter($Sentence['tokens'], function ($Word) use ($Modifier) {
                        return $Word['index'] === $Modifier;
                    }))[0];

                    // Create the array if needed
                    if (!isset($Keywords[$Keyword['lemma']]['modifiers'][$ModifierToken['lemma']])) {
                        $Keywords[$Keyword['lemma']]['modifiers'][$ModifierToken['lemma']] = [
                            'lemma' => $ModifierToken['lemma'],
                            'raw' => $ModifierToken['word'],
                            'freq' => 0,
                        ];
                    }

                    // Increment the frequency
                    $Keywords[$Keyword['lemma']]['modifiers'][$ModifierToken['lemma']]['freq']++;
                    $TotalModifiedKeywords++;
                }
            }
        }

        // Save the keywords
        $this->saveKeywords($UrlModel, $Keywords, $TotalKeywords, $TotalModifiedKeywords);
    }

    /**
     * Save the keywords into the database
     * @param App\Url $UrlModel              The article to connect the
     *                                       keywords to
     * @param Array   $Keywords              The keywords from the article
     * @param Integer $TotalKeywords         The total number of keywords found
     * @param Integer $TotalModifiedKeywords The total number of modified
     *                                       keywors found (sum freq)
     */
    private function saveKeywords($UrlModel, $Keywords, $TotalKeywords, $TotalModifiedKeywords)
    {
        /**
         * Keywords - array of these objects
         * {
         *   lemma: String,
         *   raw: String,
         *   freq: Integer,
         *   modifiers: [
         *     {
         *       lemma: String,
         *       raw: String,
         *       freq: Integer
         *     }, ...
         *   ]
         * }
         */

        // Loop over the keywords
        foreach ($Keywords as $Keyword) {
            // Check to see if the keyword exists
            $KeywordModel = Keyword::firstOrCreate([
                'lemma' => $Keyword['lemma'],
            ], [
                'raw' => $Keyword['raw'],
            ]);

            // Adjust the weight
            $Keyword['freq'] = round($Keyword['freq'] * 100 / $TotalKeywords);

            // Query for an existing relationship
            $Relationship = $KeywordModel
                ->articles
                ->find($UrlModel);

            if ($Relationship === null) {
                // Create the relationship
                $KeywordModel
                    ->articles()
                    ->save($UrlModel, [
                        'weight' => $Keyword['freq'],
                    ]);
            } elseif ($Relationship->pivot->weight !== $Keyword['freq']) {
                // Update the weight
                $Relationship
                    ->pivot
                    ->weight = $Keyword['freq'];
                $Relationship
                    ->pivot
                    ->save();
            }

            // Loop through the modifiers
            foreach ($Keyword['modifiers'] as $Modifier) {
                // Get or create the modifier keyword
                $KeywordModifierModel = Keyword::firstOrCreate([
                    'lemma' => $Modifier['lemma'],
                ], [
                    'raw' => $Modifier['raw'],
                ]);

                // Adjust the weight
                $Modifier['freq'] = round($Modifier['freq'] * 100 / $TotalModifiedKeywords);

                // Query for the model
                $ModifiedKeywordModel = KeywordModified::firstOrCreate([
                    'keyword_id' => $KeywordModel->id,
                    'modifier_id' => $KeywordModifierModel->id,
                ]);

                // Query for a relationship
                $Relationship = $ModifiedKeywordModel
                    ->articles
                    ->find($UrlModel);

                if ($Relationship === null) {
                    // Create the relationship
                    $ModifiedKeywordModel
                        ->articles()
                        ->save($UrlModel, [
                            'weight' => $Modifier['freq'],
                        ]);
                } elseif ($Relationship->pivot->weight !== $Modifier['freq']) {
                    // Update the weight
                    $Relationship
                        ->pivot
                        ->weight = $Modifier['freq'];
                    $Relationship
                        ->pivot
                        ->save();
                }
            }
        }
    }
}
