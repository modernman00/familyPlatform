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
        $profileAvatar = (isset($cleanPostData['gender']) && $cleanPostData['gender'] === "Male") ? "avatarM.png" : "avatarF.png";
        
        $tables = [
            'personal' =>  [
                'firstName' => $cleanPostData['firstName'] ?? null,
                'lastName' => $cleanPostData['lastName'] ?? null,
                'famCode' => $cleanPostData['famCode'] ?? null,
                'kids' => $cleanPostData['children'] ?? null,
                'gender' => $cleanPostData['gender'] ?? null,
                'siblings' => $cleanPostData['sibling'] ?? null,
                'day' => $cleanPostData['day'] ?? null,
                'month' => $cleanPostData['month'] ?? null,
                'year' => $cleanPostData['year'] ?? null,
                'id' => $cleanPostData['id'] ?? null,
            ],
            'work' => [
                'employmentStatus' => $cleanPostData['employmentStatus'] ?? null,
                'occupation' => $cleanPostData['occupation'] ?? null,
                'id' => $cleanPostData['id'] ?? null
            ],
            'contact' => [
                'email' => $cleanPostData['email'] ?? null,
                'country' => $cleanPostData['country'] ?? null,
                'mobile' => $cleanPostData['mobile'] ?? null,
                'id' => $cleanPostData['id'] ?? null,
            ],
            'account' => [
                'email' => $cleanPostData['email'] ?? null,
                'password' => $cleanPostData['password'] ?? null,
                'status' => 'new',
                'type' => 'member',
                'id' => $cleanPostData['id'] ?? null,
            ],
            'otherFamily' => [
                'spouse_name' => $cleanPostData['spouse_name'] ?? null,
                'spouse_mobile' => $cleanPostData['spouse_mobile'] ?? null,
                'spouse_email' => $cleanPostData['spouse_email'] ?? null,
                'father_name' => $cleanPostData['father_name'] ?? null,
                'father_mobile' => $cleanPostData['father_mobile'] ?? null,
                'father_email' => $cleanPostData['father_email'] ?? null,
                'mother_name' => $cleanPostData['mother_name'] ?? null,
                'mother_mobile' => $cleanPostData['mother_mobile'] ?? null,
                'mother_email' => $cleanPostData['mother_email'] ?? null,
                'otherFamCode' => $cleanPostData['famCode'] ?? null,
                'id' => $cleanPostData['id'] ?? null
            ],
            'post' => [
                'fullName' => $cleanPostData['firstName'] ?? null,
                'postMessage' => "Hey, welcome to your page",
                'profileImg' => $profileAvatar,
                'id' => $cleanPostData['id'] ?? null
            ],
            'comment' => [
                'fullName' => $cleanPostData['firstName'] ?? null,
                'comment' => "Your comment will show here",
                'profileImg' => $profileAvatar,
                'post_no' => 1000,
                'id' => $cleanPostData['id'] ?? null
            ],
            'profilePics' => [
                'img' => $profileAvatar,
                'id' => $cleanPostData['id'] ?? null
            ],
            'events' => [
                'eventName' => (isset($cleanPostData['firstName']) ? $cleanPostData['firstName'] : 'Unknown') . " Birthday",
                'eventDate' => $cleanPostData['eventDate'] ?? null,
                'eventType' => 'Birthday',
                'eventDescription' => (isset($cleanPostData['firstName']) ? $cleanPostData['firstName'] : 'Unknown') . " is adding another year",
                'eventFrequency' => 'Annually',
                'eventCode' => $cleanPostData['famCode'] ?? null,
                'id' => $cleanPostData['id'] ?? null
            ]
        ];

        if (!empty($cleanPostData['famCode'])) {
            $tables['user_families'] = [
                'user_id' => $cleanPostData['id'] ?? null,
                'family_code' => $cleanPostData['famCode'],
                'status' => $cleanPostData['familyStatus'] ?? 'pending',
                'role' => 'member'
            ];
        }

        return $tables;
  }





}