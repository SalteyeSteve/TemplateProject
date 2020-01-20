<?php
include 'config_vars.inc.php';
function autoloader ($class) {
    $split = explode('\\', $class);
    $location = __DIR__ .'/'. implode('/', $split) . '.php';

    if(!is_readable($location)) {
        return;
    }
    require_once $location;
}
spl_autoload_register("autoloader");
