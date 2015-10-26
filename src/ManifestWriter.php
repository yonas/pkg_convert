<?php

namespace fizk\pkg;

/**
 * Implementations of ManifestWriter creates package manifests for specific operating systems.
 */
abstract class ManifestWriter {
    protected $package = null;
    protected $package_path = null;

    public function __construct(Package $package, $package_path) {
        $this->package = $package;
        $this->package_path = $package_path;
    }

    public function create_manifest() { }
}
