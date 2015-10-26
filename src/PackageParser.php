<?php

namespace fizk\pkg;

use fizk\pkg\PackageCli;

/**
 * Implementations of PackageParser read packages for specific operating systems and return an Package object.
 */
abstract class PackageParser {
    protected $path_is_tmp;
    protected $path;
    protected $pkg;

    public function __construct($path) {
        $this->path = realpath($path);
    }

    public function __destruct() {
        if ($this->path_is_tmp === true && is_dir($this->path)) {
            PackageCli::debug("Removing temporary package directory: " . $this->path);
            PackageCli::remove_dir($this->path);
        }
    }

    public function parse_pkg() {
        $this->pre_parse();
        $this->parse();
        $this->post_parse();
        return $this->pkg;
    }

    public function get_pkg_path() {
        return $this->path;
    }

    protected function pre_parse() { }
    protected function parse() { }
    protected function post_parse() { }
}
