<?php

namespace fizk\pkg;

use fizk\pkg\PackageCli;

class FreeBSD_PackageWriter extends PackageWriter {
    private function canonicalize_path($path, $cwd=null) {
        // don't prefix absolute paths
        if (substr($path, 0, 1) === "/") {
            $filename = $path;
        }
        // prefix relative path with $root
        else {
            $root = is_null($cwd) ? getcwd() : $cwd;
            $filename = sprintf("%s/%s", $root, $path);
        }

        // get realpath of dirname
        $dirname = dirname($filename);
        $canonical = realpath($dirname);

        // trigger error if $dirname is nonexistent
        if ($canonical === false) {
            trigger_error(sprintf("Directory `%s' does not exist", $dirname), E_USER_ERROR);
        }

        // prevent double slash "//" below
        if ($canonical === "/")
            $canonical = null;

        // return canonicalized path
        return sprintf("%s/%s", $canonical, basename($filename));
    }

    public function create_package($output_dir, $format = 'txz') {
        if (!in_array($format, array('txz', 'tbz', 'tgz', 'tar'))) {
            print("Unknown package format: " . $format);
            return false;
        }

        $output_dir = $this->canonicalize_path($output_dir);

        // Parse package information
        $package = new FreeBSD_Package($this->parser->parse_pkg());

        // Add file prefix
        $package->plist = array_merge(array('@cwd ' . $package->prefix), $package->plist);

        // `pkg create` will create the files listing based on the plist, so remove it here
        unset($package->files);

        // Create FreeBSD manifest and plist files
        $manifest_writer = new FreeBSD_ManifestWriter($package, $this->parser->get_pkg_path());
        $manifest = $manifest_writer->create_manifest();

        // Move package files to be under $prefix
        $package_dir = $this->parser->get_pkg_path() . $package->prefix;
        PackageCli::debug('Creating new package directory: ' . $package_dir);
        mkdir($package_dir, 0755, true);
        PackageCli::debug('Moving contents of ' . $this->parser->get_pkg_path() . ' to new directory ' . $package_dir);
        shell_exec('mv ' . $this->parser->get_pkg_path() . '/* ' . $package_dir . '/');

        // Create FreeBSD compressed package file
        $command = 'pkg create --format ' . $format .
                   ' --out-dir ' . $output_dir .
                   ' --verbose' .
                   ' --plist ' . $manifest['plist_dir'] . '/plist' .
                   ' --metadata ' . $manifest['manifest_dir'] .
                   ' --root-dir ' . $this->parser->get_pkg_path();

        PackageCli::debug("Creating pkg file:\n". $command);
        $output = shell_exec($command);
        print_r($output);

        return true;
    }
}
