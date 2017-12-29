<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**** API ROUTES ****/
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->get('article/{url_id}/keywords', [
            'as' => 'api.v1.article',
            'uses' => 'KeywordApi@article',
        ]);

        $router->get('keyword/{keyword_id}', [
            'as' => 'api.v1.keyword',
            'uses' => 'KeywordApi@keyword',
        ]);

        $router->get('keyword_modified/{keyword_id}', [
            'as' => 'api.v1.keyword_modified',
            'uses' => 'KeywordApi@keyword_modified',
        ]);
    });
});
