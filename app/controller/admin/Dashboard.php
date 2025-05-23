<?php

declare(strict_types=1);

namespace App\Controller\admin;

class Dashboard
{

    public function index(): void
    {
        view('admin/ReviewApps');
    }
}
