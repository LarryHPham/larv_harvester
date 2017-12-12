<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles pages with no attribution
 */
trait NoAttribution
{
    protected $attributionXPath = '//div';

    protected function getAttribution()
    {
        return 'Kbb.com Editors';
    }
}
