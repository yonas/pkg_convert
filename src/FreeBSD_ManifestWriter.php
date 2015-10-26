<?php

namespace fizk\pkg;

use fizk\pkg\PackageCli;

class FreeBSD_ManifestWriter extends ManifestWriter {
    public function create_manifest($output_dir) {
        return $this->create_metadatadir($this->package, $output_dir);
    }

    // Creates +MANIFEST and plist files
    private function create_metadatadir(Package $pkg, $output_dir) {
        if (empty($pkg)) {
            print("Parsed package information is empty. Aborting package creation.\n");
            return false;
        }

        if (empty($pkg->plist)) {
            print("Parsed package information is empty. Aborting package creation.\n");
            return false;
        }

        $manifest = (array)$pkg;
        $plist = $manifest['plist'];
        unset($manifest['plist']);

        foreach ($manifest as $name => $data) {
            if (empty($data)) {
                unset($manifest[$name]);
            }
        }

        if (!is_dir($output_dir)) {
            PackageCli::debug('mkdir ' . $output_dir);
            mkdir($output_dir);
        }

        $metadatadir = $output_dir . '/metadata';
        if (!is_dir($metadatadir)) {
            PackageCli::debug('mkdir ' . $metadatadir);
            mkdir($metadatadir);
        }

        $files_dir = $output_dir . $pkg->prefix;
        if (!is_dir($files_dir)) {
            PackageCli::debug('mkdir ' . $files_dir);
            mkdir($files_dir, 0755, true);
        }

        if (!is_dir($this->package_path)) {
            print("Package path \"$this->package_path does not exist.\n");
            return false;
        }

        PackageCli::debug('Copying extracted package files in ' . $this->package_path . ' to output directory ' . $files_dir);
        shell_exec('find ' . $this->package_path . ' -type d -exec cp -Rp {} ' . $files_dir . ' \;');
        file_put_contents($metadatadir . '/+MANIFEST', json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        file_put_contents($output_dir . '/plist', join("\n", $plist));

        return true;
    }
}
