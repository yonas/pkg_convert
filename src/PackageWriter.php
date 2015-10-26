<?php

namespace fizk\pkg;

use fizk\pkg\PackageCli;

/**
 * Implementations of PackageWriter creates packages for specific operating systems.
 */
abstract class PackageWriter {
    protected $parser;

    public function __construct(PackageParser $parser) {
        $this->parser = $parser;
    }

    public function create_package($output_dir) {
        if (is_dir($output_dir)) {
            PackageCli::debug('Removing existing output directory: ' . $output_dir);
            PackageCli::remove_dir($output_dir);
        }

        PackageCli::debug('Creating new output directory: ' . $output_dir);
        mkdir($output_dir);
        $this->create($output_dir);
    }

    protected function create($output_dir) { }
}
