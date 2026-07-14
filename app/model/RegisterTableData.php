<?php
declare(strict_types=1);

namespace App\model;

final class RegisterTableData {



  /**
   * @return (int|mixed|string)[][]
   *
   * @psalm-return array{personal: array{firstName: mixed, lastName: mixed, famCode: mixed, kids: mixed, gender: 'Male'|mixed, siblings: mixed, day: mixed, month: mixed, year: mixed, id: mixed}, work: array{employmentStatus: mixed, occupation: mixed, id: mixed}, contact: array{email: mixed, country: mixed, mobile: mixed, id: mixed}, account: array{email: mixed, password: mixed, status: 'new', type: 'member', id: mixed}, otherFamily: array{spouse_name: mixed, spouse_mobile: mixed, spouse_email: mixed, father_name: mixed, father_mobile: mixed, father_email: mixed, mother_name: mixed, mother_mobile: mixed, mother_email: mixed, mother_maiden: mixed, otherFamCode: mixed, id: mixed}, post: array{fullName: mixed, postMessage: 'Hey, welcome to your page', profileImg: 'avatarF.png'|'avatarM.png', id: mixed}, comment: array{fullName: mixed, comment: 'Your comment will show here', profileImg: 'avatarF.png'|'avatarM.png', post_no: 1000, id: mixed}, profilePics: array{img: 'avatarF.png'|'avatarM.png', id: mixed}, events: array{eventName: string, eventDate: mixed, eventType: 'Birthday', eventDescription: string, eventFrequency: 'Annually', eventGroup: 'Global', eventCode: mixed, id: mixed}}
   */
  public static function createRegisterTable(array $cleanPostData): array {
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