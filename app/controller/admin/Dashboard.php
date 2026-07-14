<?php
declare(strict_types=1);

namespace App\controller\admin;

use App\Controller\BaseController;
use Src\Utility;

final class Dashboard extends BaseController
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
