<?php

namespace fizk\pkg;

use fizk\pkg\Package;
use fizk\pkg\PackageCli;

class FreeBSD_Package extends Package {
    public function verify() {
        $value_required = array('name', 'origin', 'version', 'comment', 'maintainer', 'homepage', 'abi', 'arch', 'prefix', 'description', 'categories', 'files');
        $missing = array();
        foreach ($value_required as $r) {
            if (empty($this->{$r})) {
                $missing[] = $r;
            }
        }
        if (!isset($this->dependencies)) {
            $this->dependencies = '';
        }

        if (!empty($missing)) {
            PackageCli::debug($this);
            throw new \Exception('An error occured while parsing the package: the following required package properties could not be found: ' . join(', ', $missing) . '.');
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
