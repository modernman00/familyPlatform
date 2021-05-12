<?php

namespace App\controller;


class General {

    public function sendTextToMember()
    {
        $message = "it is well";
        $number = $_POST['number'];
        sendText(message: $message, numbers:$number);
    }

     public function sendEmailToMember()
        {
            $data = json_decode(file_get_contents("php://input"), true);

            $array = [
                'viewPath' => $data['viewPath'],
                'data' => $data['data'],
                'subject' => $data['subject'],
            ];
            sendEmailGeneral($array, 'member');
            echo json_encode(['message' => "Message sent to ". $data['data']['name'] ]);
    }



}