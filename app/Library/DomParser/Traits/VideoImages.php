<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the image from a video where the image is saved in
 * a ld+json script element in the head
 */
trait VideoImages
{
    protected $imageXPath = '//script[@type="application/ld+json"]';

    protected function getImages()
    {
        // Get the JSON string
        $images = null;
        $this
            ->content
            ->filterXPath($this->imageXPath)
            ->each(function($node) use (&$images) {
                // If already found the image, don't parse this tag
                if ($images !== null) {
                    return;
                }

                // Get the json
                $json = json_decode(rtrim(trim($node->text()), ';'), true);

                // Check to see if it is the right image json
                if (!isset($json['image'])) {
                    print json_encode($json) . "\n";
                    print trim($node->text()) . "\n";
                    return;
                }

                // Loop over the images
                $images = [];
                foreach ($json['image'] as $image) {
                    $images[] = [
                        'image_title' => '',
                        'image_source_url' => $image['url'],
                        'image_width' => $image['width'],
                        'image_height' => $image['height'],
                    ];
                }
            });

        // Return the images
        return $images;
    }
}
