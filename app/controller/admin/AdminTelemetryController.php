<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use Src\Utility;

final class AdminTelemetryController extends BaseController
{
    public function index(): void
    {
        try {
            parent::viewWithCsp('admin/telemetry');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
