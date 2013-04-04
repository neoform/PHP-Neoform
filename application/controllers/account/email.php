<?php

	new account_change_email();

	class account_change_email {

		public function __construct() {

			switch (core::http()->segment(3)) {
				case 'update':
					$this->update();
					break;

				default:
					$this->display();
			}
		}

		protected function update() {

			if (core::auth()->user_id) {

				core::http()->ref();

				$json = new render_json();

				try {
					user_api::update_email(
					    core::auth()->user(),
    					[
    						'email' => core::http()->post('email'),
    					]
					);
					$json->status = 'good';
				} catch (input_exception $e) {
					$json->errors = $e->errors();
					$json->message = $e->message();
				}

				$json->render();
			} else {
				throw new redirect_login_exception('account/email');
			}
		}

		protected function display() {

			if (core::auth()->user_id) {
				$view = new render_view();

				$view->meta_title = 'Change Email / Account';
				$view->subheader  = 'Change Email';

				$view->render('account/email');
			} else {
				throw new redirect_login_exception('account/email');
			}
		}
	}

