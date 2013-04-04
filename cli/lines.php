<?php

    define('ROOT_DIR', getcwd() . '/../');

    new liner([
        'application',
        'entities',
        'library',
    ]);

    class liner {

        protected $lines = 0;
        protected $bytes = 0;
        protected $files = [];

        public function __construct(array $dirs) {
            foreach ($dirs as $dir) {
                $this->scan_dirs(ROOT_DIR . $dir);
            }

            echo 'Lines: ' . number_format($this->lines) . "\n";
            echo 'Size: ' . number_format($this->bytes / 1000, 1) . "KB\n";
        }

        protected function scan_dirs($dir) {
             if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if (substr($file, 0, 1) !== '.') {
                        $filepath = $dir . '/' . $file;

                        if (is_dir($filepath)) {
                            echo "DIR:\t" . $dir . "\n";
                            $this->scan_dirs($filepath);
                           } else {
                            echo "FILE:\t" . $filepath . "\n";
                            $this->files[] = $filepath;
                            $this->lines += (int) count(file($filepath));
                            $this->bytes += (int) filesize($filepath);
                        }
                    }
                }

                closedir($handle);
            }
        }
    }