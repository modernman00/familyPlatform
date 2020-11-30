<?php

namespace App\controller\members;
use App\model\{
    SingleCustomerData, Post
    };

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
        //printArr($data);
        $instanceAllData = new Post;
        $allData = $instanceAllData->getAllPost();
        // //foreach ($allData as $allData);
        // printArr($allData);
        $_SESSION['id'] = $custId;
        $_SESSION['fName'] = $data['firstName'];
        $_SESSION['lName'] = $data['lastName'];
        $_SESSION['currentTime'] = time();
        // printArr($data);
        view('member/profile3', compact('data', 'allData'));
    }

    function post()
    {
        $newPost = new Post;
        $getPost = $newPost->processPost();
        $getPost['id'] = $_SESSION['id'];
        $getPost['fullName'] = $_SESSION['fName']." ".$_SESSION['lName'];
        $getPost['post_time'] = $_SESSION['currentTime'];
        
        $this->insertData_NoRedirect($getPost, 'post');
        header('Location: /member/ProfilePage');


        
    }
}
