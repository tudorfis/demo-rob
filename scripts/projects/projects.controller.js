(function () {
    'use strict';

    angular.module('app.projects')
        .controller('ProjectsCtrl', ['$scope', '$rootScope', '$modal', 'ApiService', ProjectsCtrl])
        .controller('ProjectsCrudCtrl', ['$scope', '$modalInstance', 'ApiService', 'project', ProjectsCrudCtrl]);

    function ProjectsCtrl($scope, $rootScope, $modal, ApiService) {

        var s = $scope,
            rs = $rootScope;

        s.openUpdateProject = function(project) {
            var modalInstance = $modal.open({
                templateUrl: "update-project.html",
                controller: 'ProjectsCrudCtrl',
                resolve: {
                    project: function() {
                        return project;
                    }
                }
            });
        };

        s.openDeleteProject = function(project) {
            var modalInstance = $modal.open({
                templateUrl: "delete-project.html",
                controller: 'ProjectsCrudCtrl',
                resolve: {
                    project: function() {
                        return project;
                    }
                }
            });
        };

        s.init = function() {
            ApiService.get('api/getProjects').then(function(res){
                s.projects = res;
            })
        };
        s.init();

    }

    function ProjectsCrudCtrl($scope, $modalInstance, ApiService, project) {

        var s = $scope;
        var projectsCtrl = $('[data-ng-controller="ProjectsCtrl"]');

        s.project = project;

        s.updateProject = function(project) {
            ApiService.post('api/updateProject', project).then(function(res){
                if (!project.id) {
                    project.id = res.insertId;
                    projectsCtrl.scope().projects.push(project);
                }
                s.cancelModal();
            });
        };

        s.deleteProject = function(project) {
            ApiService.post('api/deleteProject', project).then(function(res){
                if (res.affectedRows == 1) {
                    angular.forEach(projectsCtrl.scope().projects, function(p, p_id){
                        if (p.id == project.id) {
                            projectsCtrl.scope().projects.splice(p_id, 1);
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