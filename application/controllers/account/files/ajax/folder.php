<?php

    core::output()->output_type('json');
    core::http()->ref();
    $json = new render_json();

    if (! core::auth()->user_id) {
        throw new redirect_login_exception();
    }

    switch (core::http()->segment(5)) {
        case 'children':

            $breadcrumb = new folder_collection;

            try {
                $folder = new folder_model((int) core::http()->parameter('id'));
                if ($folder->user_id !== core::auth()->user_id) {
                    throw new folder_exception('That folder doesn\'t exist');
                }

                // Folders
                $files = $folder->file_collection();

                // Files
                $folders = $folder->child_folder_collection();

                // Breadcrumbs
                $breadcrumb[] = $parent = $folder;
                while ($parent = $parent->parent_folder()) {
                    $breadcrumb->add($parent);
                }

            } catch (folder_exception $e) {
                // Folders
                $folders = new folder_collection(folder_dao::by_user_parent(
                    core::auth()->user_id,
                    null
                ));

                // Files
                $files = new file_collection(file_dao::by_user_folder(
                    core::auth()->user_id,
                    null
                ));
            }

            $folders->sort('name', 'asc');
            $files->sort('label', 'asc');

            $json->folders = $folders->export([
                'id',
                'name',
            ]);

            $json->files = $files->export([
               'id',
               'label',
               'size',
               'size_current',
               'posted_on',
               'md5_hash',
               'status',
            ]);

            $breadcrumbArr = $breadcrumb->export([
                'id',
                'name',
            ]);
            $breadcrumbArr[] = [
                'id'   => null,
                'name' => 'Home',
            ];
            $json->breadcrumbs = array_reverse($breadcrumbArr);

            break;

        case 'insert':
            try {
                $folder = folder_api::insert(core::http()->posts());
                $json->status = 'good';
                $json->info   = $folder->export();
            } catch (input_exception $e) {
                $json->status  = 'error';
                $json->message = $e->message();
                $json->errors  = $e->errors();
            }
            break;

        case 'update':
            try {
                $folder = new folder_model(core::http()->parameter('id'));
                $folder = folder_api::update($folder, core::http()->posts());
                $json->status = 'good';
                $json->info   = $folder->export();
            } catch (input_exception $e) {
                $json->status  = 'error';
                $json->message = $e->message();
                $json->errors  = $e->errors();
            }
            break;

        case 'delete':
            try {
                $folder = new folder_model(core::http()->parameter('id'));
                folder_api::delete($folder);
                $json->status = 'good';
            } catch (input_exception $e) {
                $json->status  = 'error';
                $json->message = $e->message();
                $json->errors  = $e->errors();
            }
            break;

        default:
            $json->status  = 'error';
            $json->message = 'Unknow action';
    }

    $json->render();