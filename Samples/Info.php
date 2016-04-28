<?php

    namespace MyApp\Controller;

    use Neoform;
    use MyApp;

    class Info extends MyApp\Controller {

        public function defaultAction() {

            $view = new Neoform\Render\Html;
            $this->applyDefaults($view);

            if ($this->request->getGet()->count() || $this->request->getParameters()->count()) {
                return $this->show404();
            }

            $view->slug = $slug = (string) $this->request->getNonControllerSlugs()->get('slug');

            $validPages = [
                'about'        => 'About Us',
                'merchants'    => 'Merchants',
                'advertising'  => 'Advertising',
                'partnerships' => 'Partnerships',
                'syndication'  => 'Syndication',
                'marketing'    => 'Marketing',
                'privacy'      => 'Privacy Policy',
                'tos'          => 'Terms of Service',
                'contact'      => 'Contact',
            ];

            if (! isset($validPages[$slug])) {
                return $this->show404();
            }

            $view->suppressMain = true;

            $view->meta_title = "{$validPages[$slug]} - Example.com";

            $view->applyTemplate('info');
            $this->response->setView($view);
        }
    }