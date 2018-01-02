<?php

namespace App\Library\NLPParser;

use StanfordNLP\Parser;
use Wamania\Snowball\English;
use App\Keyword;
use App\KeywordModified;

class KeywordParser
{
    /**
     * This variable is an instance of the SanfordNLP Parser
     * @var StanfordNLP\Parser
     */
    private $parser;

    /**
     * This variable is an instance of the Snowball Enlgish stemmer
     * @var Wamania\Snowball\English
     */
    private $stemmer;

    /**
     * This function loads the stanford parser and the stemmer
     */
    public function __construct()
    {
        // Load the parser
        $this->parser = new Parser(
            base_path('app/Library/NLPParser/stanford-parser.jar'),
            base_path('app/Library/NLPParser/stanford-english-corenlp-2017-06-09-models.jar')
        );

        // Load the stemmer
        $this->stemmer = new English();
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
        // Parse the text
        $Sentences = null;
        try {
            $Sentences = $this
                ->parser
                ->parseSentences([$Body]);
        } catch (\Exception $e) {
            // print 'ERROR: ' . $e->getMessage() . "\n";
        }

        // Check for errors
        if ($this->parser->getErrors() !== null) {
            // print $this->parser->getErrors();
        }

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
        foreach ($Sentences as $Sentence) {
            $SentenceKeywords = [];
            $SentenceKeywordIndexes = [];

            // Get the proper nouns
            foreach ($Sentence['wordsAndTags'] as $WordIndex => $Word) {
                // Filter for only proper nouns
                if (strpos($Word[1], 'NNP') === 0) {
                    // Add to the indexes
                    $SentenceKeywordIndexes[] = $WordIndex;

                    // Make an array for the dependencies
                    $Word[1] = [];

                    // Add the word to the array
                    $SentenceKeywords[] = $Word;
                }
            }

            // Get the supporting words
            foreach ($Sentence['typedDependencies'] as $Dependency) {
                // Skip non-modifying and non-compound relationships
                if (
                    strpos($Dependency['type'], 'mod') === false &&
                    strpos($Dependency['type'], 'compound') === false
                ) {
                    continue;
                }

                // Find the word being used (if it is a proper noun)
                // Use index - 1 because the index starts at 1
                $ParentIndex = array_search($Dependency[0]['index'] - 1, $SentenceKeywordIndexes);

                // Save the modifier if needed
                if ($ParentIndex !== false) {
                    $SentenceKeywords[$ParentIndex][1][] = $Dependency[1]['feature'];
                }
            }

            // Clean up and save the keywords
            foreach ($SentenceKeywords as $Keyword) {
                // Lowercase and get the stem
                $KeywordStem = $this->stemmer->stem(strtolower($Keyword[0]));

                // Make the array if needed
                if (!isset($Keywords[$KeywordStem])) {
                    $Keywords[$KeywordStem] = [
                        'stem' => $KeywordStem,
                        'raw' => $Keyword[0],
                        'freq' => 0,
                        'modifiers' => [],
                    ];
                }

                // Increment the frequency
                $Keywords[$KeywordStem]['freq']++;
                $TotalKeywords++;

                // Loop over the modifiers
                foreach ($Keyword[1] as $Modifier) {
                    // Get the stem
                    $ModifierStem = $this->stemmer->stem(strtolower($Modifier));

                    // Create the array if needed
                    if (!isset($Keywords[$KeywordStem]['modifiers'][$ModifierStem])) {
                        $Keywords[$KeywordStem]['modifiers'][$ModifierStem] = [
                            'stem' => $ModifierStem,
                            'raw' => $Modifier,
                            'freq' => 0,
                        ];
                    }

                    // Increment the frequency
                    $Keywords[$KeywordStem]['modifiers'][$ModifierStem]['freq']++;
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
         *   stem: String,
         *   raw: String,
         *   freq: Integer,
         *   modifiers: [
         *     {
         *       stem: String,
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
                'stem' => $Keyword['stem'],
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
                    'stem' => $Modifier['stem'],
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
