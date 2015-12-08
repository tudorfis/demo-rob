(function () {
    'use strict';

    angular.module('app')
        .config(['$routeProvider', function($routeProvider) {
            var routes, setRoutes;

            routes = [
                'auth/login', 'auth/register', 'auth/forgot-password', 'auth/profile'
            ];

            setRoutes = function(route) {
                var config, url;
                url = '/' + route;
                config = {
                    templateUrl: 'views/' + route + '.html'
                };
                $routeProvider.when(url, config);
                return $routeProvider;
            };

            routes.forEach(function(route) {
                return setRoutes(route);
            });

            $routeProvider
                .when('/projects', {templateUrl: 'views/projects/projects.html'})
                .when('/orders', {templateUrl: 'views/orders/orders.html'})
                .when('/products', {templateUrl: 'views/products/products.html'})
                .when('/404', {templateUrl: 'views/pages/404.html'})
                .when('/500', {templateUrl: 'views/pages/500.html'})
                .when('/', {redirectTo: 'projects'})
                .otherwise({ redirectTo: '/404'});

        }]
    );

})(); 