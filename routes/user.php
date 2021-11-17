<?php
/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api/v1/client'], function ($router) {
    $router->group([
        'prefix' => 'auth',
        'namespace' => 'Auth'
    ], function ($router){
        $router->post('login', 'AuthController@login');
        $router->group(['middleware' => 'auth:' . GUARD_CUSTOMER], function ($router) {
            $router->get('me', 'AuthController@me');
            $router->post('/logout', 'AuthController@logout');
        });
    });

    $router->group(['middleware' => 'auth:' . GUARD_CUSTOMER, 'prefix' => 'loans'], function ($router) {
        $router->get('/', 'LoanController@index');
        $router->get('/{id}', 'LoanController@show');
        $router->post('/', 'LoanController@create');
        $router->post('/{id}/update_day', 'LoanController@updateDayPayment');
        $router->post('/{id}/payment', 'LoanController@payment');
    });
});
