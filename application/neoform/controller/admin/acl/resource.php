<?php

    namespace neoform;

    class controller_admin_acl_resource extends controller_admin {

        public function default_action() {

            $view = new render\view;

            $page  = (int) http::instance()->parameter('page');
            $sort  = (string) http::instance()->parameter('sort') ?: 'id';
            $order = (string) http::instance()->parameter('order') ?: 'asc';

            $valid_sorts = ['id', 'name', ];

            if ($sort && ! in_array($sort, $valid_sorts)) {
                self::error(500, 'Invalid sort', "You cannot sort by \"{$sort}\"");
                return;
            }

            $per_page = 20;

            if ($page < 1) {
                $page = 1;
            }

            if ($id = (int) http::instance()->slug('id') ?: null) {
                $resource = new acl\resource\model($id);
            } else {
                $resource = null;
            }

            $view->resource = $resource;

            $view->meta_title = 'ACL Roles';

            $resources = new acl\resource\collection(
                entity::dao('acl\resource')->by_parent(
                    $resource ? $resource->id : null,
                    [
                        $sort => $order === 'asc' ? entity\record\dao::SORT_ASC : entity\record\dao::SORT_DESC
                    ],
                    ($page - 1) * $per_page,
                    $per_page
                )
            );
            $resources->acl_role_collection();
            $resources->child_acl_resource_collection();

            $view->resources = $resources;

            $view->page     = $page;
            $view->sorter   = new render\sort($sort, $order, $valid_sorts);
            $view->total    = entity::dao('acl\resource')->count([
                'parent_id' => $resource ? $resource->id : null,
            ]);
            $view->per_page = $per_page;

            $view->render('admin/acl/resource');
        }
    }