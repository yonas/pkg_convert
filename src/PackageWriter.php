<?php

namespace fizk\pkg;

/**
 * Implementations of PackageWriter creates packages for specific operating systems.
 */
abstract class PackageWriter {
    protected $parser;

    public function __construct(PackageParser $parser) {
        $this->parser = $parser;
    }

    public function create_package($output_dir) {
    }
}
