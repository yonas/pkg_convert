<?php

namespace fizk\pkg;

use fizk\pkg\Package;
use fizk\pkg\PackageCli;

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
            PackageCli::debug($this);
            throw new \Exception('The following required package properties could not be found: ' . join(', ', $missing) . '.');
        }

        $this->www = $this->homepage;
        unset($this->homepage);
        $this->desc = $this->description;
        unset($this->description);
        $this->deps = $this->dependencies;
        unset($this->dependencies);
        $this->shlibs_required = $this->shared_libraries_required;
        unset($this->shared_libraries_required);

        return true;
    }
}
