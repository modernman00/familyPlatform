<?php 

function printMemory ()
{
    /** Currently used memory */
    $mem_usage = memory_get_usage(true);
    /** Peak memory usage */
    $mem_peak = memory_get_peak_usage(true);

    echo "MEM_USAGE : This script is using : <strong>" .round($mem_usage / 1024) ." kb </strong> of memory". BR;
    echo "PEAK_USAGE : This script is using : <strong>" .round($mem_peak / 1024) ."kb </strong> of memory" . BR;
}