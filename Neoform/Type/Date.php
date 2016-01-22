<?php

    namespace Neoform\Type;

    use DateTime;
    use DateTimeImmutable;
    use DateTimeZone;

    class Date extends DateTimeImmutable {

        /**
         * @return string
         */
        public function __toString() {
            return $this->getHuman();
        }

        /**
         * Unix Timestamp
         *
         * @return integer
         */
        public function getUnixTimeStamp() {
            return (int) $this->format('U');
        }

        /**
         * Timestamp string
         *
         * @return string
         */
        public function getTimeStamp() {
            return $this->format('Y-m-d H:i:s');
        }

        /**
         * Return a cloned $this with the timezone changed
         *
         * @param DateTimeZone $tz
         *
         * @return date
         */
        public function getLocalized(DateTimeZone $tz) {
            $dt = clone $this;
            return $dt->setTimezone($tz);
        }

        /**
         * Date to human readable
         *
         * @param bool $show_time
         *
         * @return string
         */
        public function getHuman($show_time=true) {

            $timezone = new DateTimeZone('America/Montreal');
            $time_code = $show_time ? 'g:ia' : '';

            $self = clone $this;
            $self->setTimezone($timezone);

            $now = new DateTime('now', $timezone);
            $diff = $now->diff($self);
            $this_date = $self->format('Y-m-d');

            //past
            if ($diff->invert === 1) {
                //more than a year from now
                if ($diff->y) {
                    return $self->format('M j, Y' . ($time_code ? ", {$time_code}" : ''));
                } else {
                    //more than a month from now
                    if ($diff->m) {
                        return $self->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                    } else {

                        $today = $now->format('Y-m-d');
                        $yesterday = new DateTime('-1 day', $timezone);
                        $yesterday = $yesterday->format('Y-m-d');

                        //today
                        if ($this_date === $today) {
                            return 'Today' . ($time_code ? ' ' . $self->format($time_code) : '');
                        //tomorrow
                        } else if ($this_date === $yesterday) {
                            return 'Yesterday' . ($time_code ? ' ' . $self->format($time_code) : '');
                        //this week
                        //} else if ($diff->d < 7) {
                        //    return 'This past ' . $self->format('l'. ($time_code ? ' ' . $time_code : ''));
                        //the rest of the month
                        } else {
                            return $self->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                        }
                    }
                }

            //future
            } else {
                //more than a year from now
                if ($diff->y) {
                    return $self->format('M j, Y' . ($time_code ? ", {$time_code}" : ''));
                } else {
                    //more than a month from now
                    if ($diff->m) {
                        if ((int) $now->format('Y') === (int) $self->format('Y')) {
                            return $self->format('D M j' . ($time_code ? ", {$time_code}" : ''));
                        } else {
                            return $self->format('D M j, Y' . ($time_code ? ", {$time_code}" : ''));
                        }
                    } else {

                        $today = $now->format('Y-m-d');
                        $tomorrow = new DateTime('+1 day', $timezone);
                        $tomorrow = $tomorrow->format('Y-m-d');

                        //today
                        if ($this_date === $today) {
                            return 'Today' . ($time_code ? ' ' . $self->format($time_code) : '');
                        //tomorrow
                        } else if ($this_date === $tomorrow) {
                            return 'Tomorrow' . ($time_code ? ' ' . $self->format($time_code) : '');
                        //this week
                        } else if ($diff->d < 7) {
                            return $self->format('l' . ($time_code ? " {$time_code}" : ''));
                        //the rest of the month
                        } else {
                            return $self->format('D M j' . ($time_code ? ", {$time_code}" : ''));
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
        public function getHumanAgo() {

            $now = new DateTime;
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
        public function getAsAge() {
            $now = new DateTime;
            $age = $now->diff($this, true);

            return (int) $age->y;
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function isMorePastThan(Date $date) {
            return $this < $date;
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function isMoreFutureThan(Date $date) {
            return $this > $date;
        }

        /**
         * @param date $date
         *
         * @return bool
         */
        public function isEqual(Date $date) {
            return $this == $date;
        }

        /**
         * @return bool
         */
        public function isInThePast() {
            return $this->isMorePastThan(new Date);
        }

        /**
         * @return bool
         */
        public function isInTheFuture() {
            return $this->isMoreFutureThan(new Date);
        }

        /**
         * @param integer $seconds
         *
         * @return string
         */
        public static function secondsToHuman($seconds) {
            if ($seconds < 60) {
                return "{$seconds} second" . ($seconds === 1.0 ? '' : 's');
            } else if ($seconds < 3600) {
                $minutes = round($seconds / 60, 1);
                return "{$minutes} minute" . ($minutes === 1.0 ? '' : 's');
            } else if ($seconds < 86400) {
                $hours = round($seconds / 3600, 1);
                return "{$hours} hour" . ($hours === 1.0 ? '' : 's');
            } else if ($seconds < 2629740) {
                $days = round($seconds / 86400, 1);
                return "{$days} day" . ($days === 1.0 ? '' : 's');
            } else if ($seconds < 31556900) {
                $months = round($seconds / 2629740, 1);
                return "{$months} month" . ($months === 1.0 ? '' : 's');
            } else {
                $years = round($seconds / 31556900, 1);
                return "{$years} year" . ($years === 1.0 ? '' : 's');
            }
        }
    }

