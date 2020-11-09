<?php

namespace App\controller\cust;
use App\model\SingleCustomerData;

use App\classes\AllFunctionalities;

class ProfilePage extends AllFunctionalities
{
    function index()
    {
        $custId = checkInput($_SESSION['identifyCust']);
        $setData = new SingleCustomerData;
        $data = $setData->getCustomerData($custId);
        //var_dump($data);
        view('cust/index', compact('data'));
    }
}
