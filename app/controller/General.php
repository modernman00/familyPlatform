<?php

namespace App\Controller;
use Src\ToSendEmail;

use Exception;


class General
{

    public function sendTextToMember(): void
    {
        $message = "it is well";
        $number = $_POST['number'];
        sendText(message: $message, numbers: $number);
    }

    public function sendEmailToMember(): void
    {
        try {

             $data = json_decode(file_get_contents("php://input"), true);

        $array = [
            'viewPath' => $data['viewPath'],
            'data' => $data['data'],
            'subject' => $data['subject'],
            'familyCode'=> $data['familyCode']
        ];
       ToSendEmail::sendEmailGeneral($array, 'member');

        echo json_encode(['message' => "Message sent to " . $data['data']['name']]);

        } catch (Exception $err) {

            showError($err);

        }
       
    }

      public function sendTextMember(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        sendText(message: $data['data']['mobile'], numbers: $data['mobile']);

        echo json_encode(['message' => "Message sent to " . $data['data']['name']]);
    }




}
