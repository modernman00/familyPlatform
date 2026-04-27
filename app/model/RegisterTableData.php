<?php 

declare(strict_types=1);

namespace App\model;

class RegisterTableData {



  public static function createRegisterTable(array $cleanPostData) {
        // Access the cleanPostData directly, as it's promoted to a class property
        $profileAvatar = $cleanPostData['gender'] === "Male" ? "avatarM.png" : "avatarF.png";
        return [
            'personal' =>  [
                'firstName' => $cleanPostData['firstName'],
                'lastName' => $cleanPostData['lastName'],
                'famCode' => $cleanPostData['familyCode'],
                'kids' => $cleanPostData['kids'],
                'gender' => $cleanPostData['gender'],
                'siblings' => $cleanPostData['siblings'],
                'day' => $cleanPostData['day'],
                'month' => $cleanPostData['month'],
                'year' => $cleanPostData['year'],
                'id' => $cleanPostData['id'],
            ],
            'work' => [
                'employmentStatus' => $cleanPostData['employmentStatus'],
                'occupation' => $cleanPostData['occupation'],
                'id' => $cleanPostData['id']
            ],
            'contact' => [

                'email' => $cleanPostData['email'],
                'country' => $cleanPostData['country'],
                'mobile' => $cleanPostData['mobile'],
                'id' => $cleanPostData['id'],
            ],
            'account' => [
                'email' => $cleanPostData['email'],
                'password' => $cleanPostData['password'],
                'status' => 'new',
                'type' => 'member',
                'id' => $cleanPostData['id'],
            ],
            'otherFamily' => [
                'spouse_name' => $cleanPostData['spouse_name'],
                'spouse_mobile' => $cleanPostData['spouse_mobile'],
                'spouse_email' => $cleanPostData['spouse_email'],
                'father_name' => $cleanPostData['father_name'],
                'father_mobile' => $cleanPostData['father_mobile'],
                'father_email' => $cleanPostData['father_email'],
                'mother_name' => $cleanPostData['mother_name'],
                'mother_mobile' => $cleanPostData['mother_mobile'],
                'mother_email' => $cleanPostData['mother_email'],
                'mother_maiden' => $cleanPostData['mother_maiden'],
                'otherFamCode' => $cleanPostData['familyCode'],
                'id' => $cleanPostData['id']
            ],
            'post' => [
                'fullName' => $cleanPostData['firstName'],
                'postMessage' => "Hey, welcome to your page",
                'profileImg' => $profileAvatar,
                'id' => $cleanPostData['id']
            ],
            'comment' => [
                'fullName' => $cleanPostData['firstName'],
                'comment' => "Your comment will show here",
                'profileImg' => $profileAvatar,
                'post_no' => 1000,
                'id' => $cleanPostData['id']
            ],
            'profilePics' => [
                'img' => $profileAvatar,
                'id' => $cleanPostData['id']
            ],
            'events' => [
                'eventName' => "{$cleanPostData['firstName']} Birthday",
                'eventDate' => $cleanPostData['eventDate'],
                'eventType' => 'Birthday',
                'eventDescription' => "{$cleanPostData['firstName']} is adding another year",
                'eventFrequency' => 'Annually',
                'eventGroup' => 'Global',
                'eventCode' => $cleanPostData['familyCode'],
                'id' => $cleanPostData['id']
            ]

        ];
  }





}