<?php

	core::locale()->set_namespace('main');

	$view = new render_view();

	$view->render('index');
