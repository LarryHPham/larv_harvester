<?php

namespace SNT\ContentAwareSystem\Modules\UrlContentRender;

use Illuminate\Support\Facades\Log;
use JonnyW\PhantomJs\Client;

/**
 * Class PhantomJsUrlContentRender
 *
 * Extracts html from a url by using a local headless browser and processing javascript found on the page to generate the page's content
 *
 * @package SNT\ContentAwareSystem\Modules\UrlContentRender
 */
class PhantomJsUrlContentRenderer extends BaseUrlContentRenderer
{
    /**
     * @var int Tracks number of redirects in-flight
     */
    protected $numRedirects;

    /**
     * @var int Defines the maximum number of times a request can be redirected
     */
    protected $maxNumRedirects;

    /**
     * PhantomJsUrlContentRenderer constructor.
     */
    public function __construct()
    {
        $this->numRedirects    = 0;

        // max redirects by default is 5
        $this->maxNumRedirects = config('cas.content_render.max_redirects');
    }

    /**
     * Extracts Text from a given URL
     *
     * @param string $url Target URL for extraction
     * @return string Text extracted from URL
     * @throws \Exception
     */
    public function renderContentFromUrl($url)
    {
        $contentResponse = $this->getPageContentFromUrl($url);

        if (!empty($contentResponse['message'])) {
            throw new \Exception($contentResponse['status'] . ': ' . $contentResponse['message']);
        }

        return $contentResponse['content'];
    }

    private function getPageContentFromUrl($url)
    {
        $times_to_try = 5;
        $timeout      = 5000;
        $current_try  = 1;

        do {
            $success     = false;
            $content     = '';
            $return_data = [];

            $client = Client::getInstance();
            // phantomjs_path is usually in the '/usr/bin/phantomjs'
            $client->getEngine()->setPath(config('cas.content_render.phantomjs_path'));
            $client->getEngine()->addOption('--load-images=false');
            $client->getEngine()->addOption('--ignore-ssl-errors=true');
            $client->getProcedureCompiler()->clearCache();
            $client->getProcedureCompiler()->disableCache();
            $client->isLazy(); // Tells the client to wait for all resources before rendering

            // cas.http_user_agent "SNTMedia-Crawler/1.0 default"
            $request = $client->getMessageFactory()->createRequest($url, 'GET');
            $request->addHeader('User-Agent', config('cas.http_user_agent'));
            $request->setTimeout($timeout); // Will render page if this timeout is reached and resources haven't finished loading

            $response = $client->getMessageFactory()->createResponse();

            $client->send($request, $response);

            $status = $response->getStatus();

            Log::debug("PhantomJsUrlContentRender $url, try $current_try of $times_to_try with timeout $timeout, status returned was $status");

            if ($response->isRedirect()) {
                if ($this->numRedirects >= $this->maxNumRedirects) {
                    throw new \Exception('Exceeded maximum number of redirects');
                }
                $this->numRedirects++;

                $redirectUrl = $response->getRedirectUrl();
                Log::debug("Got redirect from $url to $redirectUrl");

                return $this->getPageContentFromUrl($redirectUrl);
            }

            switch ($status) {
                case 200:
                    $success = true;
                    $message = '';
                    $content = $response->getContent();
                    break;
                case 302: // because we don't want to retry
                    $message = 'Redirect to ' . $response->getRedirectUrl();
                    break (2); // breaks out of the switch AND the do while loop
                case 404:
                    $message = 'Page not found.';
                    break;
                case 408:
                    // request timed out. add time and try again
                    $timeout += 2500;
                    $message = 'Request timed out.';
                    break;
                default:
                    $message = 'Uncaught error.';
                    break;
            }
        } while (!$success && $current_try++ < 5);

        $return_data = [
            'success' => $success,
            'status'  => $status,
            'message' => $message,
            'content' => $content,
        ];

        return $return_data;
    }
}
