<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app/Cloud.php');
echo "Flushing All Caches At: " . date("Y-m-d H:i:s") . chr(10);
if (!Cloud::app()->getCache()->isCacheEnabled()) {
    echo "\tCache Not Enabled".chr(10);
} else {
    $backends = Cloud::app()->getCache()->getBackends(); 
    Cloud::app()->getCache()->flush();
    foreach($backends as $backend) {
        echo "\tFlushed " . end(explode("\\", get_class($backend))) . chr(10);;
    }
}
echo "Completed Cache Flush at " . date("Y-m-d H:i:s");
