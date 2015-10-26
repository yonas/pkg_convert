<?php

namespace fizk\pkg;

class OpenBSD_PackageParser extends PackageParser {
    protected $pkg;

    protected function pre_parse() {
        // If it's a .tgz, extract files
        // Verify +CONTENTS and +DESC files exist
    }

    protected function parse() {
        $contents = array_merge($this->parse_contents_file(), $this->parse_info_file());
        $this->pkg = new OpenBSD_Package($contents);
    }

    private function parse_contents_file() {
        $contents = array();
        $file = file_get_contents($this->dir . '/+CONTENTS');

        if (preg_match('/^(.+)\n[+]DESC\n((?:.+)\n@cwd[^\n]+)\n(.+)\n$/sm', $file, $matches)) {
            array_shift($matches);

            list($header, $desc, $data) = $matches;
            $contents = array_merge($this->parse_header($header), $this->parse_desc($desc), $this->parse_data($data));
        } else {
            print "No match\n";
        }

        return $contents;
    }

    private function parse_header($str) {
        $header = array();
        // Remove first line
        $str = substr($str, strpos($str, '\n'));

        // Note: this does not allow for multiple @comment lines
        if (preg_match_all('/^@([^\s]+)\s(.+)$/m', $str, $matches)) {
            $header = array_combine($matches[1], $matches[2]);

            // parse origin and categories
            if (isset($header['comment']) && preg_match('/pkgpath=([^,]+),/', $header['comment'], $matches)) {
                $header['origin'] = $matches[1];
                $header['categories'] = array(substr($matches[1], 0, strpos($matches[1], '/')));
                unset($header['comment']);
            }

            // parse name and version
            if (isset($header['name']) && preg_match('/^(.+)[-]([0-9.]+)$/', $header['name'], $matches)) {
                $header['name'] = $matches[1];
                $header['version'] = $matches[2];
            }

            // parse abi and arch
            if (isset($header['arch'])) {
                $system_arch = php_uname('m');
                $system_os_name = php_uname('s');
                $system_os_ver = php_uname('r');

                $header['abi'] = $system_os_name . ':' . $system_os_ver . ':' . $header['arch'];

                switch ($header['arch']) {
                    case 'amd64':
                        $header['arch'] = strtolower($system_os_name) . ':' . $system_os_ver . ':x86:64';

                    default:
                        unset($header['arch']);
                }
            }

/*
            // remove signer
            if (isset($header['signer'])) {
                unset($header['signer']);
            }

            // remove digital-signature
            if (isset($header['digital-signature'])) {
                unset($header['digital-signature']);
            }
*/
        }

        return $header;
    }

    private function parse_desc($str) {
        $desc = array();

        // parse dependencies
        if (preg_match_all('/^@depend (.+)$/m', $str, $matches)) {
            $desc['dependencies'] = array(); 
            foreach ($matches[1] as $dependency) {
                list($a, $b, $c) = explode(':', $dependency);
                list($category, $name) = explode('/', $a);
                $name = preg_replace('/^(.+),[-].+$/', '${1}', $name);
                $version = substr($c, strrpos($c, '-') + 1);
                
                $desc['dependencies'][$name] = array('origin' => $a, 'version' =>  $version);
            }
        }

        // parse prefix
        if (preg_match_all('/^@cwd (.+)$/m', $str, $matches)) {
            $desc['prefix'] = $matches[1][0];
        }

        // parse shared_libraries_required
        if (preg_match_all('/^@wantlib (.+)$/m', $str, $matches)) {
            $desc['shared_libraries_required'] = $matches[1];
        }

        return $desc;
    }

    private function parse_data($str) {
        $plist = array();
        $files = array();

        $obsd_commands = array('ask-update', 'bin', 'comment', 'conflict', 'cwd', 'dir', 'exec', 'exec-always', 'exec-add', 'exec-update', 'extra', 'extraunexec', 'file', 'fontdir', 'group', 'info', 'lib', 'man', 'mandir', 'mode', 'newgroup', 'newuser', 'option', 'owner', 'pkgcfl', 'pkgpath', 'rcscript', 'sample', 'shell', 'sysctl', 'sysctl', 'unexec', 'unexec-always', 'unexec-delete', 'unexec-update', 'ts', 'link', 'sha', 'mode', 'size', 'symlink', 'ts'); 
        $fbsd_commands = array('cmd', 'exec', 'unexec', 'mode', 'owner', 'group', 'comment', 'dir');
        $lines = explode("\n", $str);

        $file = '';
        $cwd = '';
        foreach ($lines as $line) {
            if (preg_match('/^@([^ ]+) (.+)$/', $line, $matches)) {
                $command = $matches[1];
                $data = $matches[2];
            }
            else if (preg_match('/^@([^ ]+)$/', $line, $matches)) {
                $command = $matches[1];
                $data = '';
            }
            else if (preg_match('/^([^ ]+)$/', $line, $matches)) {
                $command = 'file';
                $data = $matches[1];
            }
            else {
                print("Unknown command: {$command}\n");
            }
            
            if (!in_array($command, $obsd_commands)) {
                print("Unknown command: {$command}\n");
                continue;
            }

            switch ($command) {
                case 'file':
                case 'bin':
                case 'man':
                    $files[$data] = '';
                    $file = $data;
                    if (substr($data, strlen($data) - 1) == '/') {
                        $plist[] = '@dir ' . $data;
                    }
                    else {
                        $plist[] = $data;
                    }
                    break;

                case 'sha':
                    $files[$file] = '1$' . $data;
                    break;

                default:
                    if (in_array($command, $fbsd_commands)) {
                        if ($command == 'cwd') {
                            $cwd = $data;
                        }
                        $plist[] = '@' . $command . ' ' . $data;
                    }
            }
        }

        return array('files' => $files, 'plist' => $plist);
    }

    private function parse_info_file() {
        $info = array();
        $content = file_get_contents($this->dir . '/+DESC');

        // parse maintainer
        if (preg_match('/^Maintainer: (.+)$/m', $content, $matches)) {
            $info['maintainer'] = $matches[1];
        }

        // parse homepage
        if (preg_match('/^WWW: (.+)$/m', $content, $matches)) {
            $info['homepage'] = $matches[1];
        }

        // parse description
        if (preg_match('/^[^\n]+\n(.+)Maintainer:/sm', $content, $matches)) {
            $info['description'] = str_replace("\n", ' ', trim($matches[1]));
        }

        // parse comment
        $info['comment'] = substr($content, 0, strpos($content, "\n"));

        return $info;
    }
}
