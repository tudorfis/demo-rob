(function () {
    'use strict';

    angular.module('app')
        .controller('AppCtrl', [ '$scope', '$rootScope', '$route', '$document', '$location', AppCtrl]);

    
    function AppCtrl($scope, $rootScope, $route, $document, $location) {

        var s = $scope,
            rs = $rootScope;

        var date = new Date();
        var year = date.getFullYear();

        s.main = {
            brand: 'Demo Rob',
            name: 'Rob Whittlesey',
            year: year
        };

        s.pageTransitionOpts = [
            {
                name: 'Fade up',
                "class": 'animate-fade-up'
            }, {
                name: 'Scale up',
                "class": 'ainmate-scale-up'
            }, {
                name: 'Slide in from right',
                "class": 'ainmate-slide-in-right'
            }, {
                name: 'Flip Y',
                "class": 'animate-flip-y'
            }
        ];

        s.admin = {
            layout: 'wide',                                 // 'boxed', 'wide'
            menu: 'vertical',                               // 'horizontal', 'vertical', 'collapsed'
            fixedHeader: true,                              // true, false
            fixedSidebar: true,                             // true, false
            pageTransition: $scope.pageTransitionOpts[0],   // unlimited
            skin: '12'                                      // 11,12,13,14,15,16; 21,22,23,24,25,26; 31,32,33,34,35,36
        };

        s.$watch('admin', function(newVal, oldVal) {
            if (newVal.menu === 'horizontal' && oldVal.menu === 'vertical') {
                rs.$broadcast('nav:reset');
            }
            if (newVal.fixedHeader === false && newVal.fixedSidebar === true) {
                if (oldVal.fixedHeader === false && oldVal.fixedSidebar === false) {
                    s.admin.fixedHeader = true;
                    s.admin.fixedSidebar = true;
                }
                if (oldVal.fixedHeader === true && oldVal.fixedSidebar === true) {
                    s.admin.fixedHeader = false;
                    s.admin.fixedSidebar = false;
                }
            }
            if (newVal.fixedSidebar === true) {
                s.admin.fixedHeader = true;
            }
            if (newVal.fixedHeader === false) {
                s.admin.fixedSidebar = false;
            }
        }, true);

        s.color = {
            primary: '#7992BF',
            success: '#A9DC8E',
            info: '#6BD5C3',
            infoAlt: '#A085E4',
            warning: '#ECD48B',
            danger: '#ED848F',
            gray: '#DCDCDC'
        };

        rs.$on("$routeChangeSuccess", function (event, currentRoute, previousRoute) {
            $document.scrollTo(0, 0);
        });

        rs.checkIfLoggedIn = function() {
            return rs.user;
        };

        rs.logoutUser = function() {
            localStorage.removeItem('user');
            rs.user = null;
            $location.path('/auth/login');
        };

        s.init = function() {
            if (localStorage.getItem('user')) {
                rs.user = JSON.parse(localStorage.getItem('user'));
            } else {
                $location.path('/auth/login');
            }
        };
        s.init();
    }

})(); 