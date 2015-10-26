<?php

use fizk\pkg\PackageCli;

require 'vendor/autoload.php';

if (PackageCli::convert()) {
    print("Done!\n\n");
}
