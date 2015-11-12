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
            $trace = debug_backtrace(false);
            $caller = $trace[0];
            print("[DEBUG] [{$caller['file']} {$caller['function']}:{$caller['line']}] ");
            print_r($output);
            print("\n");
        }
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function create_temp_dir() {
        $tmp_dir = sys_get_temp_dir() . 'pkg-create-' . self::generateRandomString();
        if (is_dir($tmp_dir)) {
            self::remove_dir($tmp_dir);
        }

        self::debug('Creating temporary directory: ' . $tmp_dir);
        mkdir($tmp_dir);
        return $tmp_dir;
    }

    public static function remove_dir($dir) {
        self::debug('Removing directory: ' . $dir);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    public static function usage() {
        global $argv;
        print("\nUsage: " . $argv[0] . " -p|--package-path=<path to uncompressed package directory or compressed package file> [-o|--output-path=<path to output directory>] [-f|--format=<tgz|tbz|tgz|tar>] [-d|--debug]\n\n");
    }

    public static function get_options() {
        $options = array('output-path' => realpath('.'), 'format' => 'txz');
        $shortopts = 'p:o:f::d::h::';
        $longopts = array(
            'package-path:',
            'output-path::',
            'format::',
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

                case 'f':
                case 'format':
                    $options['format'] = $getopt[$opt];
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

        if (empty($options['package-path'])) {
            echo "Error: Missing package path\n";
            self::usage();
            exit(1);
        }

        return $options;
    }

    public static function convert($from = 'OpenBSD', $to = 'FreeBSD') {
        try {
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
            return $pkg->create_package($options['output-path'], $options['format']);
        }
        catch (\Exception $e) {
            print_r($e->getMessage() . "\n");
            print_r($e->getTraceAsString() . "\n");
            return false;
        }
    }
}
