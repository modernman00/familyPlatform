<?php
declare(strict_types=1);

namespace App\model;

final class RegisterTableData {



  /**
   * @param array<string, mixed> $cleanPostData
   * @return array<string, array<string, mixed>> keyed by DB table name
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
                'employmentStatus' => $cleanPostData['employmentStatus'] ?? "Not specified",
                'occupation' => $cleanPostData['occupation'] ?? "Not specified",
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
                'ai_consent' => $cleanPostData['ai_consent'] ?? 0,
                'status' => 'active',
                'type' => 'member',
                'id' => $cleanPostData['id'] ?? null,
            ],
            'otherFamily' => [
                'spouse_name' => $cleanPostData['spouse_name'] ?? "",
                'spouse_mobile' => $cleanPostData['spouse_mobile'] ?? "",
                'spouse_email' => $cleanPostData['spouse_email'] ?? "",
                'father_name' => $cleanPostData['father_name'] ?? "",
                'father_mobile' => $cleanPostData['father_mobile'] ?? "",
                'father_email' => $cleanPostData['father_email'] ?? "",
                'mother_name' => $cleanPostData['mother_name'] ?? "",
                'mother_mobile' => $cleanPostData['mother_mobile'] ?? "",
                'mother_email' => $cleanPostData['mother_email'] ?? "",
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