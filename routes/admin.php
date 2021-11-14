<?php
/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api/v1/admin'], function ($router) {
    /* Authen admin */
    $router->group(['prefix' => 'auth', 'namespace' => 'Auth'], function ($router) {
        $router->post('/login', 'AuthController@login');
        $router->group([
            'middleware' => ['auth.jwt'],
        ], function ($router) {
            $router->post('/logout', 'AuthController@logout');
            $router->get('/me', 'AuthController@me');
        });
    });

    $router->group(['middleware' => 'auth:api', 'prefix' => 'loans'], function ($router) {
        $router->get('/', 'LoanController@index');
        $router->get('/{id}', 'LoanController@show');
        $router->post('approve/{id}', 'LoanController@approve');
        $router->post('reject/{id}', 'LoanController@reject');
    });
});
