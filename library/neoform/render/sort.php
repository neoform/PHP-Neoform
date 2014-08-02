<?php

    namespace neoform\render;

    class sort {

        protected $sort;
        protected $order;
        protected $valid_sorts;
        protected $additional_params;

        /**
         * @param string     $sort
         * @param string     $order
         * @param array      $valid_sorts
         * @param array|null $additional_params
         */
        public function __construct($sort, $order, array $valid_sorts, array $additional_params=null) {
            $this->sort              = $sort;
            $this->order             = $order;
            $this->valid_sorts       = $valid_sorts;

            if ($additional_params) {
                $params = [];
                foreach ($additional_params as $k => $v) {
                    $params[] = "/{$k}:" . rawurlencode($v);
                }

                $this->additional_params = join($params);
            }
        }

        /**
         * @param string|null $col
         *
         * @return string
         */
        public function get_params($col=null) {
            if ($col === null) {
                return "/sort:{$this->sort}/order:{$this->order}{$this->additional_params}";
            } else if ($this->sort === $col) {
                return "/sort:{$this->sort}" . ($this->order === 'desc' ? '/order:asc' : '/order:desc') . $this->additional_params;
            } else {
                return "/sort:{$col}/order:asc{$this->additional_params}";
            }
        }

        /**
         * Get the sort order
         *
         * @param string $col
         *
         * @return string
         */
        public function get_order($col=null) {
            if ($col === null || $col === $this->sort) {
                return $this->order;
            }
        }
    }