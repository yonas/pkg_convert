<?php

namespace fizk\pkg;

class FreeBSD_Package extends Package {
    public function verify() {
        $required = array('name', 'origin', 'version', 'comment', 'maintainer', 'homepage', 'abi', 'arch', 'prefix', 'description', 'dependencies', 'categories', 'files');
        $missing = array();
        foreach ($required as $r) {
            if (empty($this->{$r})) {
                $missing[] = $r;
            }
        }

        if (!empty($missing)) {
                throw new Exception('The following required package properties could not be found:' . join(', ', $missing) . '.');
        }

        return true;
    }
}
