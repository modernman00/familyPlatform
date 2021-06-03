<?php

namespace App\classes;

use App\classes\JwtHandler;
use App\classes\Db;

class Auth extends JwtHandler
{

    protected $db;
    protected $headers;
    protected $token;

    public function __construct($headers)
    {
        parent::__construct();
        $this->headers = $headers;
    }

    public function isAuth()
    {
        try {
            if (array_key_exists('waleToken', $_COOKIE) && !empty(trim($this->headers))) {
                    $data = $this->jwtDecodeData($this->headers);
                    if (isset($data['auth']) && isset($data['data']->id)) {
                        $fetchData =  $this->fetchUser($data['data']->id);
                    } else {
                        msgException(401, "Could not use token to locate users");
                    }
            } else {
                msgException(501, "Header not found");
            }
            return $fetchData;
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    protected function fetchUser($user_id)
    {
        try {
            $query = "SELECT `email` FROM `account` WHERE `id`=?";
            $query_stmt = Db::connect2()->prepare($query);
            $query_stmt->execute([$user_id]);
            if ($query_stmt->rowCount()) {
                return "SUCCESSFUL";
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            showErrorExp($e);
        }
    }
}
