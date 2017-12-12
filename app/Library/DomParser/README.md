# Dom Parser Library

This library holds the files that are used to parse a DOM into an article JSON.

<!-- TOC depthFrom:1 depthTo:6 withLinks:1 updateOnSave:1 orderedList:0 -->

- [Dom Parser Library](#dom-parser-library)
	- [Usage](#usage)
	- [Extending the Parsers](#extending-the-parsers)
		- [Creating the Parser](#creating-the-parser)
		- [Traits](#traits)
			- [DRY Coding using Traits](#dry-coding-using-traits)
			- [Creating Traits](#creating-traits)
			- [No Attribution or No Publication Date](#no-attribution-or-no-publication-date)
			- [Other (Lesser Used) Options](#other-lesser-used-options)
				- [changePhotoSize](#changephotosize)
		- [Registering in ParseDom](#registering-in-parsedom)

<!-- /TOC -->

## Usage

An example usage can be found in `app\Jobs\PageFetcher` in the `handle` function.

```php
// Include the library
use App\Library\DomParser\ParseDom;

/**
 * Instantiate the library with the following parameters:
 * @param \App\Url $Url  An instance of the URL model. This should be the model
 *                       that was crawled to get the body
 * @param Strnig   $Body The result of the HTTP request to the Url
 */
$DomParser = new ParseDom($Url, $Body);

// Check for success
if ($DomParser->json === false) {
    print "The parser failed\n";
}

// Retrieve the JSON object
var_dump($DomParser->json);

// Retrieve the parser that was used
var_dump($DomParser->parserUsed);
```

## Extending the Parsers

A new parser is relatively trivial to add. At a basic level, one must create the parser and then register it in `ParseDom`.

### Creating the Parser

The parsers are stored in folders inside `app\Library\DomParser`. The folder should indicate which project the parser will be used for (e.g. `KBB`).

The parser should extend `App\Library\DomParser\BaseDomParser` and add XPaths or functions to get the elements for that DOM.


The parser MUST have the following protected attributes (or obtain them from a trait):
- title_xpath
- attribution_xpath
- publication_date_xpath
- raw_article_content_xpath
- image_xpath

A very basic parser would look like this:
```php
<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * {A brief description of the type of pages the parser works on}
 *
 * Example pages:
 * {A url that the parser would be used on}
 */
class Parser extends BaseDomParser
{
    protected $title_xpath = '{XPath to the Title elements}';
    protected $attribution_xpath = '{XPath to the attribution}';
    protected $publication_date_xpath = '{XPath to the publication date}';
    protected $raw_article_content_xpath = '{XPath to the raw article content}';
    protected $image_xpath = '{XPath to the images}';
}
```

### Traits

#### DRY Coding using Traits

In order to prevent repeating XPaths across multiple files, traits are used to make the XPath once and then have it included in the relevant classes. Traits use much the same syntax as classes.

#### Creating Traits

Traits are not able to overwrite the `metaTitleXPath`, `metaKeywordsXPath`, `metaDescriptionXPath`, or `publisher_xpath` properties but they can overwrite any function in `BaseDomParser`.

In general, any XPath that is repeated across multiple page types should be abstracted into a trait.

Here is an example of a simple trait that just adds XPaths:
```php
<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the title and article content from pages with rich
 * text divs
 */
trait RichText
{
    protected $title_xpath = '//div[contains(@class,"title-one")]//h1';
    protected $raw_article_content_xpath = '//div[contains(@class,"rich-text")]';
}
```

Here is an example of a trait that overwrites functions as well as XPaths:
```php
<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the image from a video where the image is saved in
 * a ld+json script element in the head
 */
trait VideoImages
{
    protected $image_xpath = '//script[@type="application/ld+json"]';

    protected function getImages()
    {
        // Get the JSON string
        $images = null;
        $this
            ->content
            ->filterXPath($this->image_xpath)
            ->each(function ($node) use (&$images) {
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
```

#### No Attribution or No Publication Date

Because there are many pages that don't have attribution or publication date, there are traits that can replace the `attribution_xpath` or `publication_date_xpath`.
```php
<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * {A brief description of the type of pages the parser works on}
 *
 * Example pages:
 * {A url that the parser would be used on}
 */
class Parser extends BaseDomParser
{
    use
        // This replaces $attribution_xpath
        \App\Library\DomParser\Traits\NoAttribution,
        // This replaces $publication_date_xpath
        \App\Library\DomParser\Traits\NoPublicationDate;

    protected $title_xpath = '{XPath to the Title elements}';
    protected $raw_article_content_xpath = '{XPath to the raw article content}';
    protected $image_xpath = '{XPath to the images}';
}
```

#### Other (Lesser Used) Options

##### changePhotoSize

If an attribute (or parser) defines a `changePhotoSize` function, every image object will be passed to that function and the result will be used as the image object. This can be seen in action here:
```php
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
```

### Registering in ParseDom

To register your parser in `ParseDom`, add it to the array `registered_parsers`.

The array is ordered, so the first item on the list will be the first used to match against the DOM.
