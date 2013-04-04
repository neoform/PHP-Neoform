<?php

    set_time_limit(0);
    ignore_user_abort(1);

    //core::http()->ref();
    $json = new render_json();

    switch (core::http()->server('method')) {

        case 'PUT':
            try {
                $file = file_api::put(core::http()->input()); // PUT data is passed via input

                $json->status = 'good';
                $json->file   = [
                    'id'          => $file->id,
                    'label'       => $file->label,
                    'size'        => (int) $file->size,
                    'sizeCurrent' => (int) $file->size_current,
                    'updatedOn'   => (string) $file->updated_on,
                    'status'      => $file->status,
                    'md5'         => $file->md5_hash,
                ];
            } catch (input_exception $e) {
                $json->status  = 'error';
                $json->message = $e->message();
                $json->errors  = $e->errors();
            }
            break;

        case 'POST':

            try {
                $file = file_api::post(
                    core::http()->segment(5),
                    [
                        'offset' => core::http()->segment(6),
                        'length' => core::http()->segment(7),
                        'data'   => core::http()->input(),
                    ]
                );

                $json->status = 'good';
                $json->file   = [
                    'id'          => $file->id,
                    'label'       => $file->label,
                    'size'        => (int) $file->size,
                    'sizeCurrent' => (int) $file->size_current,
                    'updatedOn'   => (string) $file->updated_on,
                    'status'      => $file->status,
                    'md5'         => $file->md5_hash,
                ];
            } catch (model_exception $e) {
                $json->status  = 'error';
                $json->message = 'That file does not exist';
                $json->errors  = [];
            } catch (input_exception $e) {
                $json->status  = 'error';
                $json->message = $e->message();
                $json->errors  = $e->errors();
            }
            break;
    }

    $json->render();

