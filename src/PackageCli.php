<?php

namespace fizk\pkg;

class PackageCli { 
    private static $debug_enabled = false;

    public static function disable_debug() {
        self::$debug_enabled = false;
    }

    public static function enable_debug() {
        self::$debug_enabled = true;
    }

    public static function debug($output) {
        if (self::$debug_enabled) {
            print('[DEBUG] ');
            print_r($output);
            print("\n");
        }
    }

    public static function usage() {
        global $argv;
        print($argv[0] . " -p|--package-path <path to uncompressed package directory or compressed package file> [-o|--output-path <path to output directory>] [-d|--debug]\n\n");
    }

    public static function get_options() {
        $options = array('output-path' => realpath('.'));
        $shortopts = 'p:o:d::h::';
        $longopts = array(
            'package-path:',
            'output-path::',
            'debug::',
            'help::',
        );

        $getopt = getopt($shortopts, $longopts);
        if (empty($getopt) || $getopt === false) {
            self::usage();
            exit(1);
        }

        foreach (array_keys($getopt) as $opt) {
            switch ($opt) {
                case 'p':
                case 'package-path':
                    $options['package-path'] = $getopt[$opt];
                    break;

                case 'o':
                case 'output-path':
                    $options['output-path'] = $getopt[$opt];
                    break;

                case 'd':
                case 'debug':
                    $options['debug'] = true;
                    self::enable_debug();
                    break;

                case 'h':
                case 'help':
                    self::usage();
                    break;

                default:
                    self::usage();
                    exit(1);
            }
        }

        return $options;
    }

    public static function convert($from = 'OpenBSD', $to = 'FreeBSD') {
        $class_from = '\\fizk\\pkg\\' . $from . '_PackageParser';
        $class_to = '\\fizk\\pkg\\' . $to . '_PackageWriter';

        function __autoload($class_name) {
            if (($pos = strrpos($class_name, '\\')) !== false) {
                include 'src/' . substr($class_name, $pos + 1) . '.php';
            } else {
                include 'src/' . $class_name . '.php';
            }
        }

        $options = self::get_options();
        $pkg = new $class_to(new $class_from($options['package-path']));
        return $pkg->create_package($options['output-path']);
    }
}
