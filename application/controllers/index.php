<?php



    if (core::http()->get_isset('hello')) {
        $path = core::path('application') . '/data/sample2.wav';

        core::output()
            ->header("Content-Type: image/png")
            ->body((string) (new audio_wav($path)));
    } else {

        core::locale()->set_namespace('main');

        $view = new render_view();

        $view->render('index');

    }