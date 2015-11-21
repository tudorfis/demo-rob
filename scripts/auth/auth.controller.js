(function () {
    'use strict';

    angular.module('app.auth')
        .controller('AuthCtrl', ['$scope', '$rootScope', '$location', 'ApiService', AuthCtrl]);
        

    function AuthCtrl($scope, $rootScope, $location, ApiService) {

        var s = $scope,
            rs = $rootScope;

        s.hide_fp = 0;
        s.hide_register = 0;

        s.login = function() {
            ApiService.get('api/getUser', s.user).then(function(res){
                if (res.user) {
                    rs.user = res.user;
                    localStorage.setItem('user', JSON.stringify(rs.user));
                    $location.path('/dashboard')
                } else {
                    s.alert = {
                        show: 1,
                        class: 'alert alert-danger',
                        message: '* Invalid credentials, please try again !'
                    }
                }
            })
        };

        s.forgotPassword = function() {
            ApiService.get('api/getUser', s.user).then(function(res){
                if (res.user) {
                    s.hide_fp = 1;
                    s.alert = {
                        show: 1,
                        class: 'alert alert-success',
                        message: 'Successfully sent you email with reset information ! If not received, please check the "SPAM" folder as well'
                    }
                } else {
                    s.alert = {
                        show: 1,
                        class: 'alert alert-warning',
                        message: "* This email isn't registered in our database, make sure to write a valid email"
                    }
                }
            })
        };

        s.register = function() {
            ApiService.post('api/saveUser', s.user).then(function(res){
                if (res.is_error) {
                    s.alert = {
                        show: 1,
                        class: 'alert alert-warning',
                        message: res.error_message
                    }
                } else {
                    s.hide_register = 1;
                    s.alert = {
                        show: 1,
                        class: 'alert alert-success',
                        message: '<i class="fa fa-check"></i> Successfully registered !'
                    }
                }
            });
        };

        s.init = function() {
            if (rs.user) {
                $location.path('/dashboard');
            }    
        };
        s.init();

    }

})();