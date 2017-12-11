<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles cases where the attribution and publication date are both
 * in a div with the class "by-line"
 */
trait ByLine
{
    protected $attributionXPath = '//div[contains(@class,"by-line")]';
    protected $publicationDateXPath = '//div[contains(@class,"by-line")]';

    protected function getAttribution()
    {
        $value = $this->getUsingXPath($this->attributionXPath);

        if ($value === '' || $value === null) {
            return null;
        }

        return str_replace('by ', '', trim(explode('|', $value)[0]));
    }

    protected function getPublicationDate()
    {
        $value = $this->getUsingXPath($this->publicationDateXPath);

        if ($value === '' || $value === null) {
            return null;
        }

        $value = explode('|', $value)[1];

        return (new \Carbon\Carbon($value))->timestamp;
    }
}
