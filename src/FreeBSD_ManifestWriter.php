<?php

namespace fizk\pkg;

use fizk\pkg\PackageCli;

class FreeBSD_ManifestWriter extends ManifestWriter {
    protected $tmp_dir;

    public function __destruct() {
        if (!empty($this->tmp_dir) && is_dir($this->tmp_dir)) {
            PackageCli::remove_dir($this->tmp_dir);
        }
    }

    public function create_manifest() {
        return $this->create_metadatadir($this->package);
    }

    // Creates +MANIFEST and plist files
    private function create_metadatadir(Package $pkg) {
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

        $this->tmp_dir = PackageCli::create_temp_dir();
        $metadatadir = $this->tmp_dir . '/metadata';
        PackageCli::debug('mkdir ' . $metadatadir);
        mkdir($metadatadir);

        PackageCli::debug('Writing +MANIFEST file to ' . $metadatadir . '/+MANIFEST');
        file_put_contents($metadatadir . '/+MANIFEST', json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        PackageCli::debug('Writing plist file to ' . $this->tmp_dir . '/plist');
        file_put_contents($this->tmp_dir . '/plist', join("\n", $plist));

        return array('manifest_dir' => $metadatadir, 'plist_dir' => $this->tmp_dir);
    }
}
