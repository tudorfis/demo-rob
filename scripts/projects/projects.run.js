(function(){
    'use strict';
    angular.module('app.projects')
        .run(['$rootScope', function($rootScope) {
            var rs = $rootScope;
            rs.statusProject = {
                1: {
                    v: 'Pending',
                    class: 'label label-info'
                },
                2: {
                    v: 'Due',
                    class: 'label label-primary'
                },
                3: {
                    v: 'Blocked',
                    class: 'label label-danger'
                },
                4: {
                    v: 'Suspended',
                    class: 'label label-warning'
                }
            };
        }]);
})();