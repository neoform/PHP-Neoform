<?php

    namespace neoform;

    class controller_admin_acl_resource_edit extends controller_admin {

        public function default_action() {

            $resource = new acl\resource\model((int) http::instance()->parameter('id'));

            $view = new render\view;
            $view->meta_title = 'Edit Resource';
            $view->resource   = $resource;

            $view->roles = new acl\role\collection(null, entity::dao('acl\role')->all(), 'id');

            $view->root_resources = new acl\resource\collection(
                entity::dao('acl\resource')->by_parent(null, [ 'name' => entity\dao::SORT_ASC, ])
            );

            $view->render('admin/acl/resource/edit');
        }
    }