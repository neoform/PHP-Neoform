<?php

    class email_lib {

        public static function send($template, $notification_id, user_member_model $recipient, array $args=null) {

            if (notification_lib::is_active($recipient->user(), $notification_id)) {
                $user = $recipient->user();

                $email = new email_model($template, 'default', null);
                $email->unsub_url = notification_lib::unsubscribe_url($user->email);

                foreach ($args as $k => $v) {
                    $email->$k = $v;
                }

                try {
                    $email->send($recipient->name . ' <' . $user->email . '>');
                } catch (exception_mail $e) {

                }
            }
        }
    }