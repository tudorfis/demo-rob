(function () {
    'use strict';

    angular.module('app')
        .service('ApiService', ['$http', '$q', '$rootScope', ApiService]);


    function ApiService($http, $q, $rootScope) {

        var self = this,
            rs = $rootScope;

        this.get = function (table, data) {
            var deffered = $q.defer(),
                qString = '?table='+table+'&';
            angular.forEach(data, function(value, key){
                qString += 'filter['+ key  +']=' + value +'&';
            });
            qString = qString.substring(0, qString.length - 1);
            $http({
                method: 'GET',
                url: 'api'+qString
            }).then(function (res) {
                if (res.status == 200) {
                    deffered.resolve(res.data);
                }
            }, function (err_res) {
                console.error(err_res);
            });
            return deffered.promise;
        };

        this.post = function (table, data) {
            var deffered = $q.defer(),
                qString = '?table='+table+'&';
            angular.forEach(data, function(value, key){
                if (key != '$$hashKey') {
                    qString += 'post['+ key  +']=' + value +'&';
                }
            });
            qString = qString.substring(0, qString.length - 1);
            $http({
                method: 'GET',
                url: 'api'+qString
            }).then(function (res) {
                console.log(res);
                if (res.status == 200) {
                    deffered.resolve(res.data);
                }
            }, function (err_res) {
                console.error(err_res);
            });
            return deffered.promise;
        };

        this.delete = function (table, data) {
            var deffered = $q.defer(),
                qString = '?table='+table+'&';
            if (data.id) {
                qString += 'delete[id]=' + data.id;
                $http({
                    method: 'GET',
                    url: 'api'+qString
                }).then(function (res) {
                    console.log(res);
                    if (res.status == 200) {
                        deffered.resolve(res.data);
                    }
                }, function (err_res) {
                    console.error(err_res);
                });
            } else {
                deffered.resolve(null);
            }
            return deffered.promise;
        }

    }

})();