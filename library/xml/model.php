<?php

    class xml_model {

        protected $xml;
        protected $xPath;

        public function __construct($xml_string=null) {

            $this->xml = new DOMDocument();

            if (! $xml_string || ! $this->xml->loadXML($xml_string)) {
                throw new xml_exception('Invalid xml');
            }
            $this->xPath = new domxpath($this->xml);
        }

        public function register_namespace($k) {
            $this->xPath->registerNamespace($k, $this->xml->lookupNamespaceUri($this->xml->namespaceURI));
        }

        public function get($path) {
            if ($status = $this->xPath->query($path)->item(0)) {
                return $status->nodeValue;
            }
        }

        public function set($path, $value=null, array $attributes=null) {
            $parent = $this->xml;
            $p = '';

            foreach (preg_split('`/`', $path, 0, PREG_SPLIT_NO_EMPTY) as $nodeName) {
                $p .= '/' . $nodeName;
                $element = $this->xPath->query($p)->item(0);
                if ($element === null) {
                    $element = $this->xml->createElement($nodeName);
                    $parent->appendChild($element);
                }

                $parent = $element;
            }

            if ($value !== null) {
                $element->nodeValue = $value;
            }

            if ($attributes !== null) {
                foreach ($attributes as $k => $v) {
                    $element->setAttribute($k, $v);
                }
            }
            return $element;
        }

        public function __toString() {
            return (string) $this->xml->saveXML();
        }
    }
