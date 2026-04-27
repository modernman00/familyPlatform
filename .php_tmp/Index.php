<?php

namespace App\Controller;

use App\model\Post;
use Src\{Utility, SelectFn};

class Index 
{
    public function index(): void
    {

        try {
            Utility::view('index');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    /**
     * the launch page
     * @return void 
     */
    function launch()
    {
        Utility::view('launch');
    }

    public function privacy(): void
    {
        Utility::view('privacy');
    }

    public function terms(): void
    {
        Utility::view('termOfUse');
    }

    public static function getEmails()
    {
        $result = SelectFn::selectColumnByIdentifier('account', 'email', 'status', 'approved');

        msgSuccess(200, $result);

    }

    public static function checking()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        $maxDuration = 300; // 5 minutes
        $startTime = time();

        while (true) {
            $getUnpublishedPost = Post::getUnpublishedPost();
            p($getUnpublishedPost);
            echo ": heartbeat\n\n";
        }

    }
}
