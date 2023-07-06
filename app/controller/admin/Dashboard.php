<?php

declare(strict_types=1);

namespace App\controller\admin;

class Dashboard
{

    public function index(): void
    {
        view('admin/ReviewApps');
    }
}
