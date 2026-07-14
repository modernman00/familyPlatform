<?php
namespace App\controller;

use App\model\Post;
use Src\{Utility, SelectFn};

final class Index 
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

    public function contact(): void
    {
        try {
            Utility::view('contact');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    public static function getEmails(): void
    {
        $result = SelectFn::selectColumnByIdentifier('account', 'email', 'status', 'approved');

        msgSuccess(200, $result);

    }

    public static function checking(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

         // 5 minutes
        time();

        while (true) {
            $getUnpublishedPost = Post::getUnpublishedPost();
            p($getUnpublishedPost);
            echo ": heartbeat\n\n";
        }

    }
}
