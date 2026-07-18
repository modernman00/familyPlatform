<?php
declare(strict_types=1);

namespace App\controller\admin;

use App\Controller\BaseController;
use Src\Utility;

final class Dashboard extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $verifyJWT = \Src\functionality\SignIn::verify();
        if (is_null($verifyJWT) || empty($verifyJWT['id'])) {
            throw new \Src\Exceptions\UnauthorisedException("Unauthorized access to administrative dashboard.");
        }
    }

    public function index(): void
    {
        try {
                parent::viewWithCsp('admin/ReviewApps');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    
  
    }
}
