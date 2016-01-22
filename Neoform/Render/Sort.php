<?php

    namespace Neoform\Render;

    class Sort {

        /**
         * @var string
         */
        protected $sort;

        /**
         * @var string
         */
        protected $order;

        /**
         * @var string[]
         */
        protected $additionalParams;

        /**
         * @param string     $sort
         * @param string     $order
         * @param array|null $additionalParams
         */
        public function __construct($sort, $order, array $additionalParams=null) {
            $this->sort       = $sort;
            $this->order      = $order;

            if ($additionalParams) {
                $params = [];
                foreach ($additionalParams as $k => $v) {
                    $params[] = "/{$k}:" . rawurlencode($v);
                }

                $this->additionalParams = join($params);
            }
        }

        /**
         * @param string|null $col
         *
         * @return string
         */
        public function get_params($col=null) {
            if ($col === null) {
                return "/sort:{$this->sort}/order:{$this->order}{$this->additionalParams}";
            } else if ($this->sort === $col) {
                return "/sort:{$this->sort}" . ($this->order === 'desc' ? '/order:asc' : '/order:desc') . $this->additionalParams;
            } else {
                return "/sort:{$col}/order:asc{$this->additionalParams}";
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