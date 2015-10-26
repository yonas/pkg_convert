<?php

namespace fizk\pkg;

abstract class Package {
    public $name;           // "bacula-bat"
    public $origin;         // "sysutils/bacula-bat"
    public $version;        // "7.0.5_3"
    public $comment;        // "Network backup solution (GUI)"
    public $maintainer;     // "dvl@FreeBSD.org"
    public $homepage;       // "http://www.bacula.org/"
    public $abi;            // "FreeBSD:10:amd64"
    public $arch;           // "freebsd:10:x86:64"
    public $prefix;         // "/usr/local"
    public $flatsize;       // 1842691
    public $licenselogic;   // "single"
    public $description;    // "Bacula is a set of computer programs..."
    public $dependencies;   // array("lzo2" => array("origin" => "archivers/lzo2", "version" => "2.09"))
    public $categories;     // array('sysutils') 
    public $users;          // array('bacula')
    public $groups;         // array('bacula')
    public $shared_libraries_required;   // array("libbac-7.0.5.so", "libQtCore.so.4")
    public $plist;          // a list of files in plist format
    public $files;          // array( "/usr/local/etc/bacula/bat.conf.sample" => "1$c83e309c3418ea6be556544b0ea59c3ddacdd481f6d6e69d278df5a4cfc0ee46" )
    public $scripts;        // array( 'pre-install' => array(), 'post-install' => array(), 'install' => array(), 'pre-deinstall' => array(),
                            //        'post-deinstall' => array(), 'deinstall' => array(), 'pre-upgrade' => array(), 'post-upgrade' => array(), 'upgrade' => array())

    public function __construct($contents = array()) {
        if (!empty($contents)) {
            foreach ($contents as $name => $data) {
                if (property_exists(get_class($this), $name)) {
                    $this->{$name} = $data;
                }
            }

            $this->verify();
        }
    }

    protected function verify() { }
}
