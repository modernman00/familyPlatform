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
        // if($this->headers === null){
        //     if(isset($_SERVER['Authorization'])) {
        //         $this->headers = trim($_SERVER['Authorization']);
        //     } elseif(function_exists('getallheaders')){
        //         $reqHeader = getallheaders();
        //         $reqHeader = array_combine(array_map('ucwords', array_keys($reqHeader)), array_values($reqHeader));
        //         if(isset($reqHeader['Authorization'])) {
        //             $this->headers = trim($reqHeader['Authorization']);
        //         }
        //     }
        // }

    }

    public function isAuth()
    {
        try {
                  if (array_key_exists('Authorization', $this->headers) && !empty(trim($this->headers['Authorization']))) {
            $this->token = explode(" ", trim($this->headers['Authorization']));

            if (isset($this->token[1]) && !empty(trim($this->token[1]))) {
                $data = $this->jwtDecodeData($this->token[1]);

                if (isset($data['auth']) && isset($data['data']->id) && $data['auth']) {
                    $fetchData =  $this->fetchUser($data['data']->id);
                } else{
                    msgException(401,"Could not use token to locate users");
                }
            } else{
                msgException(401, "Header found but token missing");
            }
        } else {
            msgException(501, "Header not found");
        }
        return $fetchData;
        } catch (\Throwable $th) {
            showError($th);
        }
  
    }

    protected function fetchUser($user_id)
    {
        try {
            $query = "SELECT `email` FROM `account` WHERE `id`=?";
            $query_stmt = Db::connect2()->prepare($query);
            $query_stmt->execute([$user_id]);
            if ($query_stmt->rowCount()) {
                //$row = $query_stmt->fetchAll();
                // return [
                //     'message' => 'SUCCESSFUL',
                //     'status' => 200,
                //     'user' => $row
                // ];
                return "SUCCESSFUL";
            } else {
                   return null;
            }

        } catch (\PDOException $e) {
            showError($e);
        }
    }
}
