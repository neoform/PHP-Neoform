<?php

    namespace neoform;

    class controller_admin_acl_resource_ajax_dialog extends controller_admin_acl_resource_ajax {

        public function default_action() {

            if (! auth::instance()->logged_in()) {
                throw new redirect\login\exception;
            }

            switch ((string) http::instance()->slug('action')) {
                case 'move':
                    $resource = new acl\resource\model((int) http::instance()->parameter('id'));
                    $root_resources = new acl\resource\collection(
                        entity::dao('acl\resource')->by_parent(null, [ 'name' => entity\dao::SORT_ASC, ])
                    );

                    (new render\dialog('admin/acl/resource/move'))
                        ->title('Move Resource')
                        ->css([
                            'width' => '600px',
                        ])
                        ->set_param('resource', $resource)
                        ->set_param('root_resources', $root_resources)
                        ->content('body')
                        ->content('foot')
                        ->callback('afterLoad')
                        ->callback('afterShow')
                        ->render();
                    break;

                default:

                    break;
            }
        }
    }