<?php

namespace App\controller\cust;
use App\model\SingleCustomerData;

use App\classes\AllFunctionalities;

class ProfilePage extends AllFunctionalities
{
    function index()
    {
        // printArr($_SESSION);
        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);
        $custId = checkInput($_SESSION['memberId']);
        $setData = new SingleCustomerData;
        $data = $setData->getCustomerData($custId);
        // view('cust/index', compact('data'));
        view('cust/latestIndex', compact('data'));
    }
}
