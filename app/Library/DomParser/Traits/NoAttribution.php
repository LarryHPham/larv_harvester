<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles pages with no attribution
 */
trait NoAttribution
{
    protected $attribution_xpath = '//div';

    protected function getAttribution()
    {
        return 'Kbb.com Editors';
    }
}
