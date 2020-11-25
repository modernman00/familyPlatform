<?php

namespace App\controller\members;
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
        // view('member/index', compact('data'));
        // view('member/latestIndex', compact('data'));
        view('member/profile3', compact('data'));
    }
}
