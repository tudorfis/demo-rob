(function () {
    'use strict';

    angular.module('app.products')
        .controller('ProductsCtrl', ['$scope', '$rootScope', '$modal', 'ApiService', ProductsCtrl])
        .controller('ProductsCrudCtrl', ['$scope', '$modalInstance', 'ApiService', 'product', ProductsCrudCtrl]);

    function ProductsCtrl($scope, $rootScope, $modal, ApiService) {

        var s = $scope,
            rs = $rootScope;

        /** check user role */
        if ([1].indexOf(rs.user.role) == -1) {
            window.location.href = "#/";
        }

        s.openUpdateProduct = function(product) {
            var modalInstance = $modal.open({
                templateUrl: "update-product.html",
                controller: 'ProductsCrudCtrl',
                resolve: {
                    product: function() {
                        return product;
                    }
                }
            });
        };

        s.openDeleteProduct = function(product) {
            var modalInstance = $modal.open({
                templateUrl: "delete-product.html",
                controller: 'ProductsCrudCtrl',
                resolve: {
                    product: function() {
                        return product;
                    }
                }
            });
        };

        s.init = function() {
            ApiService.get('api/getProducts').then(function(res){
                s.products = res;
            })
        };
        s.init();

    }

    function ProductsCrudCtrl($scope, $modalInstance, ApiService, product) {

        var s = $scope;
        var productsCtrl = $('[data-ng-controller="ProductsCtrl"]');

        s.product = product;

        s.updateProduct = function(product) {
            if (typeof product.date_created == 'object') {
               product.date_created = product.date_created.toISOString().substring(0, 10);
            }
            ApiService.post('api/updateProduct', product).then(function(res){
                if (!product.id) {
                    product.id = res.insertId;
                    productsCtrl.scope().products.push(product);
                }
                s.cancelModal();
            });
        };

        s.deleteProduct = function(product) {
            ApiService.post('api/deleteProduct', product).then(function(res){
                if (res.affectedRows == 1) {
                    angular.forEach(productsCtrl.scope().products, function(p, p_id){
                        if (p.id == product.id) {
                            productsCtrl.scope().products.splice(p_id, 1);
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