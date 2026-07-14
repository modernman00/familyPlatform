<?php

declare(strict_types=1);

namespace App\controller;

use App\model\{ SingleCustomerData, };
use Src\{SelectFn, Utility};
use Src\Db;
use Src\Exceptions\NotFoundException;
use Src\functionality\{ SignIn, };

class BaseController
{
    // use construct to check if user is logged in
    public function __construct()
    {
        $VerifyJWT = SignIn::verify();
        if (!\is_array($VerifyJWT) || empty($VerifyJWT['id'])) {
            throw new NotFoundException('Error Request_2');
        }
        $id = checkInput($VerifyJWT['id']);

        $_SESSION['id'] = $id;
        $query = "SELECT famCode FROM personal WHERE id = ?";
        $stmt = Db::connect2()->prepare($query);
        $stmt->execute([$id]);
        $famCode = $stmt->fetchColumn();
        $_SESSION['famCode'] = $famCode;
    }
    public static function viewWithCsp(string $view, array $data = []): void
    {
        Utility::view($view, $data);
    }

    public static function membersData(): array
    {
        $id = checkInput($_SESSION['id']);
        $setData = new SingleCustomerData;
        $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics', 'work'];

        $memberData = $setData->getCustomerData($id, $table);
        if (!$memberData || empty($memberData['famCode']) || empty($memberData['firstName']) || empty($memberData['lastName'])) {
            throw new NotFoundException('Unauthorized: No Data Found');
        }

        sessSetMany([
            'famCode' => $memberData['famCode'],
            'fullName' => $memberData['firstName'] . ' ' . $memberData['lastName']
        ]);
        $memberData['id'] = $id;
        return self::formatPerson($memberData);
    }

    private static function fetchRelationsData(string $id, string $table, string $who): ?array
{
    $data = [];
    $seen = [];

    $otherFamily = SelectFn::selectAllRowsById($table, 'id', $id) ?? [];
    if (empty($otherFamily)) {
        return null;
    }

    foreach ($otherFamily as $row) {

        $email  = $row["{$who}_email"]  ?? '';
        $mobile = $row["{$who}_mobile"] ?? '';

        // 1) Try by email
        $fetchId = [];
        if ($email !== '') {
            $fetchId = SelectFn::selectAllRowsById('contact', 'email', $email) ?? [];
        }

        // 2) If not found by email, try by mobile
        if (empty($fetchId) && $mobile !== '') {
            $fetchId = SelectFn::selectAllRowsById('contact', 'mobile', $mobile) ?? [];
        }

        // 3) If found in contact table, pull full member data
        if (!empty($fetchId)) {
            foreach ($fetchId as $getData) {
                $relId = $getData['id'] ?? null;
                if (!$relId) {
                    continue;
                }

                // ✅ don't return the same person as their own sibling/child/etc
                if ($relId === $id) {
                    continue;
                }

                // ✅ dedupe (same relative found twice)
                if (isset($seen[$relId])) {
                    continue;
                }
                $seen[$relId] = true;

                $fullData = BaseController::findMemberById($relId);

                if (isset($fullData['gender'])) {
                    $fullData['relationship'] = self::resolveRelationship($who, $fullData['gender']);
                }

                $data[] = $fullData;
            }

            continue;
        }

        // 4) Fallback (not linked)
        $relationship = self::resolveRelationship($who, $row["{$who}_gender"] ?? '');

        $sex = (($row["{$who}_gender"] ?? '') === 'Male' || $who === 'father')
            ? 'avatarM.png'
            : 'avatarF.png';

        $data[] = [
            'fullName'     => $row["{$who}_name"] ?? '',
            'email'        => $email,
            'mobile'       => $mobile,
            'linked'       => $row["{$who}_linked"] ?? '',
            'relationship' => $relationship,
            'gender'       => $row["{$who}_gender"] ?? '',
            'img'          => "/resources/images/profile/$sex",
        ];
    }

    return $data;
}




    /**
     * @param array|null|string $id
     */
    public static function findMemberById(array|string|null $id): array
    {
        $setData = new SingleCustomerData;
        $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics', 'work'];

        $memberData = $setData->getCustomerData($id, $table);
        if (!$memberData || empty($memberData['famCode']) || empty($memberData['firstName']) || empty($memberData['lastName'])) {
            throw new NotFoundException('Unauthorized: No Data Found');
        }

        return self::formatPerson($memberData);
    }

    public static function returnId(): array|string|null
    {
        return checkInput($_SESSION['id']);
    }

    /**
     * Format main person's data from multiple tables
     * @param array $data
     * @return array
     */
    private static function formatPerson(array $data): array
    {
        $sex = $data['gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';


        return [
            'id' => $data['id'] ?? '',
            'firstName' => $data['firstName'] ?? '',
            'lastName' => $data['lastName'] ?? '',
            'fullName' => $data['firstName'] . ' ' . $data['lastName'] ?? '',
            'famCode' => $data['famCode'] ?? '',
            'gender' => $data['gender'] ?? '',
            'birthDate' => \sprintf('%d-%s-%d', $data['year'] ?? 0, $data['month'] ?? '', $data['day'] ?? 0),
            'maritalStatus' => $data['marital_status'] ?? '',
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'country' => $data['country'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'day' => $data['day'] ?? null,
            'month' => $data['month'] ?? null,
            'year' => $data['year'] ?? null,
            'profilePics' => $data['img'] ?? null,
            'img' => $data['img'] ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex",


        ];
    }

    /**
     * Resolve relationship label based on role and gender.
     *
     * @param string $role    Relationship type (e.g. 'sibling', 'children')
     * @param string|null $gender Gender value (e.g. 'Male', 'Female')
     * @return string|null
     */
    private static function resolveRelationship(string $role, ?string $gender): ?string
    {
        // Define mappings for each role
        $map = [
            'sibling' => [
                'Male' => 'Brother',
                'Female' => 'Sister',
            ],
            'children' => [
                'Male' => 'Son',
                'Female' => 'Daughter',
            ],
            'sibling_children' => [
                'Male' => 'Nephew',
                'Female' => 'Niece',
            ],
            // Extendable: add 'parent', 'spouse', etc.
        ];

        // Defensive: check if mapping exists
        return $map[$role][$gender] ?? null;
    }

    /**
     * Fetch sibling children
     * @param array $siblings
     * @param string $famCode
     * @return array
     */
    private static function getSiblingChildren(array $siblings): array
    {
        $siblingChildren = [];
        foreach ($siblings as $sibling) {
            $id = $sibling['id'];

            // printArr($sibling);

            if (empty($id))
                continue;

            $children = SelectFn::selectAllRowsById('children', 'id', $id) ?? [];
            //  printArr($children);

            if (!empty($children)) {

                foreach ($children as $data) {

                    $email = $data["children_email"];

                    // check for children detail via contact 
                    $fetchTheId = SelectFn::selectAllRowsById('contact', 'email', $email);


                    if (!empty($fetchTheId)) {

                        foreach ($fetchTheId as $getData) {
                            $fullData = BaseController::findMemberById($getData['id']);


                            // set relationship for children and siblings
                            if (isset($fullData['gender'])) {
                                $fullData['relationship'] = self::resolveRelationship('sibling_children', $fullData['gender']);
                                $fullData['father_id'] = $id;
                            }

                            $siblingChildren[] = $fullData;
                        }
                    } else {
                        $sex = $data['children_gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';
                        $siblingChildren[] = [
                            'father_id' => $id,
                            'fullName' => $data['children_name'],
                            'email' => $data['children_email'],
                            'gender' => $data['children_gender'],
                            'relationship' => self::resolveRelationship('sibling_children', $data['children_gender']),
                            // 'mobile' => $data['children_mobile'],
                            'linked' => $data['children_linked'],
                            'img' => $data['img'] ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex"
                        ];
                    }
                }
            }
        }
        return $siblingChildren;
    }



    private static function hasProperImage(array $person): bool
    {
        $defaultAvatars = ['avatarF.png', 'avatarM.png'];

        // accept both profilePics and profilepics
        $profile = $person['profilePics'] ?? $person['profilepics'] ?? '';

        if (!empty($profile)) {
            return !in_array($profile, $defaultAvatars, true);
        }

        $img = $person['img'] ?? '';
        if (!empty($img)) {
            foreach ($defaultAvatars as $avatar) {
                if (strpos($img, $avatar) !== false) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }




}
