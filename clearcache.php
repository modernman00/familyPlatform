<?php 

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared!<br>";
} else {
    echo "OPcache is not enabled.<br>";
}

// Clear ACPu (if you have it)
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "APCu cache cleared!<br>";
}
?>
