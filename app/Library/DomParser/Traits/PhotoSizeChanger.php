<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles pages where the image needs to be resized
 */
trait PhotoSizeChanger
{
    protected function changePhotoSize($image)
    {
        $image['image_source_url'] = preg_replace(['/\/\d{2,3}x\d{2,3}\//', '/\?[^\/]*$/'], ['/480x360/'], $image['image_source_url']);
        $image['image_width'] = 480;
        $image['image_height'] = 360;

        return $image;
    }
}
