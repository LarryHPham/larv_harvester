<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the publication date from a "time-stamp" span
 */
trait Posted
{
    protected $publicationDateXPath = '//span[contains(@class,"time-stamp")]';

    protected function getPublicationDate()
    {
        $value = $this->getTextUsingXPath($this->publicationDateXPath);

        if ($value === '' || $value === null) {
            return null;
        }

        return (new \Carbon\Carbon(str_replace('Posted', '', $value)))->timestamp;
    }
}
