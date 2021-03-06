<?php

namespace App\Jobs;

use App\Url;
use App\Library\NLPParser\KeywordParser as NlpKeywordParser;

class KeywordParser extends Job
{
    /**
     * The model of the URL to use
     * @var App\Url
     */
    private $url_model;

    /**
     * The text of the article
     * @var string
     */
    private $url_text;

    /**
     * Save the parts that control the job
     * @param Url    $Url  The URL model
     * @param string $Body The URL text
     */
    public function __construct(Url $Url, $Body)
    {
        $this->url_model = $Url;
        $this->url_text = $Body;
    }

    /**
     * Parse the text into keywords and save them in the database
     */
    public function handle()
    {
        // Parse the keywords
        $parser = new NlpKeywordParser();
        $parser->parse($this->url_model, $this->url_text);
    }
}
