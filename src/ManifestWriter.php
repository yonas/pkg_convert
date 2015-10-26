<?php

namespace fizk\pkg;

/**
 * Implementations of ManifestWriter creates package manifests for specific operating systems.
 */
abstract class ManifestWriter {
    protected $package = null;
    protected $pkg_path = null;

    public function __construct(Package $package, $pkg_path) {
        $this->package = $package;
        $this->pkg_path = $pkg_path;
    }

    public function create_manifest($output_dir) {
    }
}
