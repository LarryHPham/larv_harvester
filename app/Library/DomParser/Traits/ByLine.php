<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles cases where the attribution and publication date are both
 * in a div with the class "by-line"
 */
trait ByLine
{
    protected $attribution_xpath = '//div[contains(@class,"by-line")]';
    protected $publication_date_xpath = '//div[contains(@class,"by-line")]';

    protected function getAttribution()
    {
        $value = $this->getTextUsingXPath($this->attribution_xpath);

        if ($value === '' || $value === null) {
            return null;
        }

        return str_replace('by ', '', trim(explode('|', $value)[0]));
    }

    protected function getPublicationDate()
    {
        $value = $this->getTextUsingXPath($this->publication_date_xpath);
        if ($value === '' || $value === null) {
            return null;
        }
        $value = explode('|', $value)[1];
        // echo $value;
        return (new \Carbon\Carbon($value))->timestamp;
    }
}
