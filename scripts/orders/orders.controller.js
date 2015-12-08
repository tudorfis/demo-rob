(function () {
    'use strict';

    angular.module('app.orders')
        .controller('OrdersCtrl', ['$scope', '$rootScope', '$modal', 'ApiService', OrdersCtrl])
        .controller('OrdersCrudCtrl', ['$scope', '$modalInstance', 'ApiService', 'order', OrdersCrudCtrl]);

    function OrdersCtrl($scope, $rootScope, $modal, ApiService) {

        var s = $scope,
            rs = $rootScope;

        /** check user role */
        if ([1,2].indexOf(rs.user.role) == -1) {
            window.location.href = "#/";
        }

        s.openUpdateOrder = function(order) {
            var modalInstance = $modal.open({
                templateUrl: "update-order.html",
                controller: 'OrdersCrudCtrl',
                resolve: {
                    order: function() {
                        return order;
                    }
                }
            });
        };

        s.openDeleteOrder = function(order) {
            var modalInstance = $modal.open({
                templateUrl: "delete-order.html",
                controller: 'OrdersCrudCtrl',
                resolve: {
                    order: function() {
                        return order;
                    }
                }
            });
        };

        s.init = function() {
            ApiService.get('api/getOrders').then(function(res){
                s.orders = res;
            })
        };
        s.init();

    }

    function OrdersCrudCtrl($scope, $modalInstance, ApiService, order) {

        var s = $scope;
        var ordersCtrl = $('[data-ng-controller="OrdersCtrl"]');

        s.order = order;

        s.updateOrder = function(order) {
            if (typeof order.date_created == 'object') {
               order.date_created = order.date_created.toISOString().substring(0, 10);
            }
            ApiService.post('api/updateOrder', order).then(function(res){
                if (!order.id) {
                    order.id = res.insertId;
                    ordersCtrl.scope().orders.push(order);
                }
                s.cancelModal();
            });
        };

        s.deleteOrder = function(order) {
            ApiService.post('api/deleteOrder', order).then(function(res){
                if (res.affectedRows == 1) {
                    angular.forEach(ordersCtrl.scope().orders, function(p, p_id){
                        if (p.id == order.id) {
                            ordersCtrl.scope().orders.splice(p_id, 1);
                        }
                        return false;
                    });
                    s.cancelModal()
                }
            });
        };

        s.closeAlert = function(index) {
            s.alert = null;
        };

        s.cancelModal = function() {
            $modalInstance.dismiss("cancel");
        };

    }

})();