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
            $root      = is_null($cwd) ? getcwd() : $cwd;
            $filename  = sprintf("%s/%s", $root, $path);
        }

        // get realpath of dirname
        $dirname   = dirname($filename);
        $canonical = realpath($dirname);

        // trigger error if $dirname is nonexistent
        if ($canonical === false) {
            trigger_error(sprintf("Directory `%s' does not exist", $dirname), E_USER_ERROR);
        }

        // prevent double slash "//" below
        if ($canonical === "/") $canonical = null;

        // return canonicalized path
        return sprintf("%s/%s", $canonical, basename($filename));
    }

    public function create_package($output_dir, $format = 'txz') {
        if (!in_array($format, array('txz', 'tbz', 'tgz', 'tar'))) {
            print("Unknown package format: " . $format);
            return false;
        }

        $output_dir = $this->canonicalize_path($output_dir);

        $package = new FreeBSD_Package($this->parser->parse_pkg());

        // Parse package information
        $manifest_writer = new FreeBSD_ManifestWriter($package, $this->parser->get_pkg_path());
        PackageCli::debug('PackageWriter output_dir: ' . $output_dir);
        $manifest_writer->create_manifest($output_dir);

        // TODO:
        // pkg create --format txz --out-dir /tmp/test2 --verbose --plist /home/yonas/packages/obsd/test/plist --metadata /home/yonas/packages/obsd/test/metadata --root-dir /home/yonas/packages/obsd/test/usr/local

        return true;
    }
}
