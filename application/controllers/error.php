<?php

    core::locale()->set_namespace('main');

    switch (core::http()->segment(2)) {

        case 400:
        case '400':
        case 'bad_request':
            core::output()->http_status_code(400);
            $view             = new render_view();
            $view->meta_title = core::locale()->translate('400 - Bad Request');                                  // 3968
            $view->pre_header = core::locale()->translate('Error 400...');                                       // 3969
            $view->header     = core::locale()->translate('Bad Request');                                        // 3970
            $view->body       = core::locale()->translate('The request cannot be fulfilled due to bad syntax.'); // 3971
            $view->render('error');
            break;

        case 401:
        case '401':
        case 'unauthorized':
            core::output()->http_status_code(401);
            $view             = new render_view();
            $view->meta_title = core::locale()->translate('401 - Unauthorized');                               // 3968
            $view->pre_header = core::locale()->translate('Error 401...');                                     // 3969
            $view->header     = core::locale()->translate('Unauthorized');                                     // 3970
            $view->body       = core::locale()->translate('You do not have authorization to view this page.'); // 3971
            $view->render('error');
            break;

        case 403:
        case '403':
        case 'access_denied':
            core::output()->http_status_code(403);
            $view             = new render_view();
            $view->meta_title = core::locale()->translate('403 - Forbidden');                               // 3972
            $view->pre_header = core::locale()->translate('Error 403...');                                  // 3973
            $view->header     = core::locale()->translate('Access Denied');                                 // 3974
            $view->body       = core::locale()->translate('You do not have permission to view this page.'); // 3975
            $view->render('error');
            break;

        case 404:
        case '404':
        case 'not_found':
            core::output()->http_status_code(404);
            $view             = new render_view();
            $view->meta_title = core::locale()->translate('404 - Page Not Found');                   // 3968
            $view->pre_header = core::locale()->translate('Error 404...');                           // 3969
            $view->header     = core::locale()->translate('Page Not Found');                         // 3970
            $view->body       = core::locale()->translate('The requested page could not be found.'); // 3971
            $view->render('error');
            break;

        case 500:
        case '500':
        case 'server_error':
            core::output()->http_status_code(500);
            $view             = new render_view();
            $view->meta_title = core::locale()->translate('500 - Internal Server Error');               // 3976
            $view->pre_header = core::locale()->translate('Error 500...');                              // 3977
            $view->header     = core::locale()->translate('Server Error');                              // 3978
            $view->body       = core::locale()->translate('There was a problem generating that page.'); // 3979
            $view->render('error');
            break;

        default:
            core::output()->redirect(
                core::locale()->route('error/server_error')
            );
    }



