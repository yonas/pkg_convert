<?php

namespace fizk\pkg;

/**
 * Implementations of PackageParser read packages for specific operating systems and return an Package object.
 */
abstract class PackageParser {
    protected $dir;
    protected $pkg;

    public function __construct($dir) {
        $this->dir = realpath($dir);
    }

    public function parse_pkg() {
        $this->pre_parse();
        $this->parse();
        $this->post_parse();
        return $this->pkg;
    }

    public function get_pkg_path() {
        return $this->dir;
    }

    protected function pre_parse() { }
    protected function parse() { }
    protected function post_parse() { }
}
