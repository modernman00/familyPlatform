<?php

use Philo\Blade\Blade;

    function view($path, array $data = []) {
        // 1st param = accept the path to the file we actually want to load
        //2nd param array = any data that we want to pass to the view
        $view = __DIR__."/../../resources/view";
        $cache = __DIR__."/../../bootstrap/cache";
        $blade = new Blade($view, $cache);
        // 2 parameters
        //requires a path to the view -> folder where views are located
        // 2nd is the path to a cache directory -> cache under bootstrap
          echo $blade->view()->make($path, $data)->render();
    }
