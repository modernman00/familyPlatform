<?php

declare(strict_types=1);

namespace App\Controller\admin;

use App\Controller\BaseController;
use Src\Utility;

class Dashboard extends BaseController
{

    public function index(): void
    {
        try {
                parent::viewWithCsp('admin/ReviewApps');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    
  
    }
}
