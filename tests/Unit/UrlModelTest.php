<?php

namespace Tests\Unit;

use App\Url;
use Faker\Factory as Faker;

class UrlModelTest extends \TestCase
{
    /**
     * Verify the model can be created
     */
    public function testCanBeCreated()
    {
        // Instantiate the class
        $url = new Url();

        // Verify it is a class
        $this->assertInstanceOf(Url::class, $url);
    }

    /**
     * Verify that protocol is required to save the URL
     */
    public function testProtocolRequired()
    {
        // Array of test urls
        $TestUrls = [
            null,
            'test.com',
            'test.com/testing',
            'http.com',
            'https//test.com',
        ];

        // Loop over the test URLs
        foreach ($TestUrls as $Url) {
            $e = null;
            try {
                (new Url([
                    'article_url' => $Url,
                ]))->save();
            } catch (\Exception $e) {
            }

            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /**
     * Verify findByHash returns a model
     */
    public function testFindByHash()
    {
        // Create a model
        $url = new Url([
            'article_url' => 'https://this.is.a.test',
        ]);

        // Save and then find the model
        $url->save();
        $url_found = Url::findByHash($url->article_url);

        // Verify the found URL matches the created one
        $this->assertEquals($url->id, $url_found->id);

        // Delete the model
        $url->delete();
    }

    /**
     * Verify that createHash works as expected
     */
    public function testCreateHash()
    {
        // Array of test URLs
        $TestUrls = [
            'test' => 'test',
            'hello' => 'hello',
            'https://test.com' => 'test.com',
            'http://letter.com/test?var=value&test=value&other=percent%20encoded' => 'letter.com/test?var=value&test=value&other=percent%20encoded',
        ];

        // Loop over the test URLs
        foreach ($TestUrls as $Url => $ToHash) {
            $this->assertEquals(Url::createHash($Url), md5($ToHash));
        }
    }

    /**
     * Verify the sanitizeURL function checks the domain
     */
    public function testSanitizeUrlDomainCheck()
    {
        // Array of test URLs
        $TestUrls = [
            'https://no-match.com/other_stuff' => false,
            'https://no.match.com/this_thing_here' => false,
            'https://match.com/query_here' => true,
        ];

        foreach ($TestUrls as $Url => $Result) {
            // Get the sanitized version
            $Sanitized = Url::sanitizeUrl($Url, 'https://match.com/test', true);

            // Check the results
            if ($Result) {
                $this->assertEquals($Sanitized, $Url);
            } else {
                $this->assertEquals($Sanitized, $Result);
            }
        }
    }

    /**
     * Verify the sanitizeUrl function removes port
     */
    public function testSanitizeUrlRemovePort()
    {
        // Array of test URLs
        $TestUrls = [
            'https://localhost:8080' => 'https://localhost',
            'http://test.com:9090/other_stuff?and=some&parameters=here' => 'http://test.com/other_stuff?and=some&parameters=here',
        ];

        foreach ($TestUrls as $Url => $Result) {
            $Sanitized = Url::sanitizeUrl($Url);

            $this->assertEquals($Sanitized, $Result);
        }
    }

    /**
     * Verify the sanitizeUrl function removes trailing slashes
     */
    public function testSanitizeUrlRemoveTrailingSlash()
    {
        // Array of test URLs
        $TestUrls = [
            'https://localhost/' => 'https://localhost',
            'http://test.com/other_stuff/?and=some&parameters=here' => 'http://test.com/other_stuff?and=some&parameters=here',
            'http://test.com/just_a_path/' => 'http://test.com/just_a_path',
        ];

        foreach ($TestUrls as $Url => $Result) {
            $Sanitized = Url::sanitizeUrl($Url);

            $this->assertEquals($Sanitized, $Result);
        }
    }

    /**
     * Verify the sanitizeUrl function removes unwanted query parameters
     */
    public function testSanitizeUrlRemoveQueryParameters()
    {
        // Array of test URLs
        $TestUrls = [
            'https://localhost?vehicleclass=test' => 'https://localhost',
            'http://test.com/other_stuff?and=some&parameters=here&Lp=test' => 'http://test.com/other_stuff?and=some&parameters=here',
            'http://test.com/just_a_path?intent=remove_me' => 'http://test.com/just_a_path',
            'http://test.com/just_a_path?intent=remove_me&Lp=test&keep=me&LNX=random' => 'http://test.com/just_a_path?keep=me',
        ];

        foreach ($TestUrls as $Url => $Result) {
            $Sanitized = Url::sanitizeUrl($Url);

            $this->assertEquals($Sanitized, $Result);
        }
    }

    /**
     * Verify the sanitizeUrl function alphabetizes parameters
     */
    public function testSanitizeUrlAlphabetizeQueryParameters()
    {
        // Array of test URLs
        $TestUrls = [
            'https://localhost?a=test&b=test&c=test' => 'https://localhost?a=test&b=test&c=test',
            'http://test.com/other_stuff?b=test&and=some&parameters=here' => 'http://test.com/other_stuff?and=some&b=test&parameters=here',
            'http://test.com/just_a_path?z=last&a=first&keep=me&b=second' => 'http://test.com/just_a_path?a=first&b=second&keep=me&z=last',
        ];

        foreach ($TestUrls as $Url => $Result) {
            $Sanitized = Url::sanitizeUrl($Url);

            $this->assertEquals($Sanitized, $Result);
        }
    }

    /**
     * Verify the sanitizeUrl function returns false if the URL is the same as
     * the parent
     */
    public function testSanitizeUrlPreventSameAsParent()
    {
        // Array of test URLs
        $TestUrls = [
            'http://test.com/path_here?a=test&b=test&c=test',
            'http://test.com/path_here/?a=test&b=test&c=test',
            'http://test.com/path_here?b=test&a=test&c=test',
            'http://test.com:80/path_here?a=test&b=test&c=test',
        ];

        foreach ($TestUrls as $Url) {
            $Sanitized = Url::sanitizeUrl($Url, 'http://test.com/path_here?a=test&b=test&c=test');

            $this->assertEquals($Sanitized, false);
        }
    }
}
