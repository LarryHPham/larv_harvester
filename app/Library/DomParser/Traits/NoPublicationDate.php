<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles pages with no publication date
 */
trait NoPublicationDate
{
    protected function getPublicationDate()
    {
        return '';
    }
}
