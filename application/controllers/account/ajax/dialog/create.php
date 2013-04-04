<?php

    if (core::auth()->user_id) {
        $json = new render_json();
        $json->status = 'close';
        $json->render();
    } else {            
        (new render_dialog('account/create'))
            ->title('Create Account') 
            ->css([
                'width' => '600px',
            ])
            ->content('body')
            ->content('foot')
            ->callback('afterLoad')
            ->render();
    }