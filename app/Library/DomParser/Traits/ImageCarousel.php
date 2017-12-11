<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles pages with a carousel of images
 */
trait ImageCarousel
{
    protected $imageXPath = '//div[contains(@class,"js-aem-gallery")]';

    protected function getImages()
    {
        // Get the JSON string
        $jsonString = $this
            ->content
            ->filterXPath($this->imageXPath)
            ->first()
            ->attr('data-images');

        // Parse the JSON
        $images = json_decode($jsonString, true)['slides'];

        // Build the images
        foreach ($images as &$image) {
            $image = [
                'image_source_url' => $image['path'],
                'image_width' => null,
                'image_height' => null,
                'image_title' => $image['title'],
            ];
        }
        unset($image);

        return $images;
    }
}
