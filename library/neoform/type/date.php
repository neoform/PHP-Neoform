<?php

    namespace neoform\type;

    use datetime;
    use datetimezone;

    class date extends datetime {

        /**
         * @param string       $datetime
         * @param datetimezone $timezone
         */
        public function __construct($datetime = 'now', datetimezone $timezone = null) {
            if ($timezone !== null) {
                parent::__construct($datetime, $timezone);
            } else {
                parent::__construct($datetime);
            }
        }

        /**
         * @return string
         */
        public function __tostring() {
            return $this->human();
        }

        /**
         * Unix Timestamp
         *
         * @return integer
         */
        public function unix_timestmap() {
            return (int) $this->format('U');
        }

        /**
         * Timestamp string
         *
         * @return string
         */
        public function timestmap() {
            return $this->format('Y-m-d H:i:s');
        }

        /**
         * Date to human readable
         *
         * @param bool $show_time
         *
         * @return string
         */
        public function human($show_time=true) {
            $time_code = $show_time ? 'g:ia' : '';

            $now = new datetime;
            $diff = $now->diff($this);
            $this_date = $this->format('Y-m-d');

            //past
            if ($diff->invert === 1) {
                //more than a year from now
                if ($diff->y) {
                    return $this->format('M j, Y' . ($time_code ? ", {$time_code}" : ''));
                } else {
                    //more than a month from now
                    if ($diff->m) {
                        return $this->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                    } else {

                        $today = $now->format('Y-m-d');
                        $yesterday = new datetime;
                        $yesterday->modify('-1 day');
                        $yesterday = $yesterday->format('Y-m-d');

                        //today
                        if ($this_date === $today) {
                            return 'Today' . ($time_code ? ' ' . $this->format($time_code) : '');
                        //tomorrow
                        } else if ($this_date === $yesterday) {
                            return 'Yesterday' . ($time_code ? ' ' . $this->format($time_code) : '');
                        //this week
                        //} else if ($diff->d < 7) {
                        //    return 'This past ' . $this->format('l'. ($time_code ? ' ' . $time_code : ''));
                        //the rest of the month
                        } else {
                            return $this->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                        }
                    }
                }

            //future
            } else {
                //more than a year from now
                if ($diff->y) {
                    return $this->format('M j, Y' . ($time_code ? ", {$time_code}" : ''));
                } else {
                    //more than a month from now
                    if ($diff->m) {
                        if ((int) $now->format('Y') === (int) $this->format('Y')) {
                            return $this->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                        } else {
                            return $this->format('D M j, Y' . ($time_code ? ", {$time_code}" : ''));
                        }
                    } else {

                        $today = $now->format('Y-m-d');
                        $tomorrow = new DateTime();
                        $tomorrow->modify('+1 day');
                        $tomorrow = $tomorrow->format('Y-m-d');

                        //today
                        if ($this_date === $today) {
                            return 'Today' . ($time_code ? ' ' . $this->format($time_code) : '');
                        //tomorrow
                        } else if ($this_date === $tomorrow) {
                            return 'Tomorrow' . ($time_code ? ' ' . $this->format($time_code) : '');
                        //this week
                        } else if ($diff->d < 7) {
                            return $this->format('l' . ($time_code ? " {$time_code}" : ''));
                        //the rest of the month
                        } else {
                            return $this->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                        }
                    }
                }
            }
        }

        /**
         * Do nothing
         */
        public function __invoke() {
            //$args = func_get_args();
        }

        /**
         * @return string
         */
        public function ago() {

            $now = new datetime;
            $ago = $now->diff($this);

            //past
            if ($ago->invert === 1) {
                if ($ago->y) {
                    if ($ago->y === 1) {
                        return 'a year ago';
                    } else {
                        return "{$ago->y} years ago";
                    }
                } else if ($ago->m) {
                    if ($ago->m === 1) {
                        return 'a month ago';
                    } else {
                        return "{$ago->m} months ago";
                    }
                } else if ($ago->d) {
                    if ($ago->d === 1) {
                        return 'yesterday';
                    } else if ($ago->d < 7) {
                        return 'on ' . $this->format('l');
                    } else if ($ago->d < 14) {
                        return 'a week ago';
                    //    return 'last ' . $this->format('l');
                    } else if ($ago->d < 21) {
                        return '2 weeks ago';
                    } else {
                        return "{$ago->d} days ago";
                    }
                } else if ($ago->h) {
                    if ($ago->h === 1) {
                        return 'about an hour ago';
                    } else {
                        return "{$ago->h} hours ago";
                    }
                } else if ($ago->i) {
                    if ($ago->i === 1) {
                        return 'a minute ago';
                    } else if ($ago->i === 2) {
                        return 'a couple of minutes ago';
                    } else if ($ago->i === 3) {
                        return 'a few minutes ago';
                    } else {
                        return "{$ago->i} minutes ago";
                    }
                } else if ($ago->s) {
                    if ($ago->s === 1) {
                        return 'a second ago';
                    } else if ($ago->s === 2) {
                        return 'a couple of seconds ago';
                    } else if ($ago->s === 3) {
                        return 'a few seconds ago';
                    } else {
                        return "{$ago->s} seconds ago";
                    }
                } else {
                    return 'right now';
                }

            //future
            } else {
                if ($ago->y) {
                    if ($ago->y === 1) {
                        return 'in a year';
                    } else {
                        return "in {$ago->y} years";
                    }
                } else if ($ago->m) {
                    if ($ago->m === 1) {
                        return 'in a month';
                    } else {
                        return "in {$ago->m} months";
                    }
                } else if ($ago->d) {
                    if ($ago->d === 1) {
                        return 'tomorrow';
                    } else {
                        return "in {$ago->d} days";
                    }
                } else if ($ago->h) {
                    if ($ago->h === 1) {
                        return 'in about an hour';
                    } else {
                        return "{$ago->h} hours";
                    }
                } else if ($ago->i) {
                    if ($ago->i === 1) {
                        return 'in a minute';
                    } else if ($ago->i === 2) {
                        return 'in a couple of minutes';
                    } else if ($ago->i === 3) {
                        return 'in a few minutes';
                    } else {
                        return "in {$ago->i} minutes";
                    }
                } else if ($ago->s) {
                    if ($ago->s === 1) {
                        return 'in a second';
                    } else if ($ago->s === 2) {
                        return 'in a couple of seconds';
                    } else if ($ago->s === 3) {
                        return 'in a few seconds';
                    } else {
                        return "in {$ago->s} seconds";
                    }
                } else {
                    return 'right now';
                }
            }
        }

        /**
         * @return int
         */
        public function age() {
            $now = new datetime();
            $age = $now->diff($this, true);

            return (int) $age->y;
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function older_than(date $date) {
            return $this->timestmap() < $date->timestmap();
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function newer_than(date $date) {
            return $this->timestmap() > $date->timestmap();
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function equals(date $date) {
            return $this->timestmap() === $date->timestmap();
        }

        /**
         * @return bool
         */
        public function is_past() {
            return $this->older_than(new date);
        }

        /**
         * @return bool
         */
        public function is_future() {
            return $this->newer_than(new date);
        }
    }

