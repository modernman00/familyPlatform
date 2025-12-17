<?php

declare(strict_types=1);

namespace App\Controller\members;

use App\controller\BaseController;
use App\model\SingleCustomerData;

use Src\{SelectFn};
use Src\Exceptions\NotFoundException;
use Src\functionality\SignIn;


class Organogram extends SingleCustomerData
{
    /**
     * Fetch full family data for the main person
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function index($id): void
    {
        try {
 
            // Sanitize input
            $id = checkInput($id ?? '');
            if (empty($id)) {
                throw new NotFoundException('Main person ID required');
            }

           $data = BaseController::findMemberById($id);


            // Fetch other family members
            $spouse = $this->fetchRelationsData($id, 'otherFamily', 'spouse');
            $father = $this->fetchRelationsData($id, 'otherFamily', 'father');
            $mother = $this->fetchRelationsData($id, 'otherFamily', 'mother');
            $siblings = $this->fetchRelationsData($id, 'sibling', 'sibling');
            $children = $this->fetchRelationsData($id, 'children', 'children');

            // Fetch sibling children
            $siblingChildren = null;

            if ($siblings !== null) {   // strict null check
               $siblingChildren = $this->getSiblingChildren($siblings); // or whatever you need to do
            }

            // Build response
            $orgData = [
                'spouse' => $spouse[0],
                'father' => $father[0],
                'mother' => $mother[0],
                'siblings' => $siblings,
                'children' => $children,
                'sibling_children' => $siblingChildren
           
            ];

         view('member/organogram', compact('orgData', 'data'));

            // msgSuccess(200, $response);
        } catch (\Throwable $th) {
            showError($th); // From helper.php
        }
    }

    private function fetchRelationsData(string $id, string $table, string $who)
    {
        $data = [];
        $otherFamily = SelectFn::selectAllRowsById($table, 'id', $id) ?? [];
        if (empty($otherFamily)) return null;


        foreach ($otherFamily as $row) {
            $email = $row["{$who}_email"];
            if (isset($email)) {
                $fetchTheId = SelectFn::selectAllRowsById('contact', 'email', $email);

                if (!empty($fetchTheId)) {

                    foreach ($fetchTheId as $getData) {
                        $fullData = BaseController::findMemberById($getData['id']);

                        // set relationship for children and siblings
                        if (isset($fullData['gender'])) {
                            $fullData['relationship'] = $this->resolveRelationship($who, $fullData['gender']);
                        }

                        $data[] = $fullData;
                    }
                } else {
                    $relationship = $fullData['relationship'] = $this->resolveRelationship($who, $row["{$who}_gender"]);
                    $sex = $row['gender'] === 'Male' || $who === 'father' ? 'avatarM.png' : 'avatarF.png';
                    $data[] = [
                        'fullName' => $row["{$who}_name"],
                        'email' => $row["{$who}_email"],
                        'mobile' => $row["{$who}_mobile"],
                        'linked' => $row["{$who}_linked"],
                        'relationship' => $relationship,
                        'gender' => $row["{$who}_gender"],
                        'img' => $row['img'] ? "/resources/images/profile/{$row['img']}" : "/resources/images/profile/$sex"
                    ];
                }
            }
        }

        return $data;
    }



    /**
     * Fetch sibling children
     * @param array $siblings
     * @param string $famCode
     * @return array
     */
    private function getSiblingChildren(array $siblings): array
    {
        $siblingChildren = [];
        foreach ($siblings as $sibling) {
            $id = $sibling['id'];

            // printArr($sibling);

            if (empty($id)) continue;

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
                                $fullData['relationship'] = $this->resolveRelationship('sibling_children', $fullData['gender']);
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
                            'relationship' => $this->resolveRelationship('sibling_children', $data['children_gender']),
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

    /**
     * Resolve relationship label based on role and gender.
     *
     * @param string $role    Relationship type (e.g. 'sibling', 'children')
     * @param string|null $gender Gender value (e.g. 'Male', 'Female')
     * @return string|null
     */
    private function resolveRelationship(string $role, ?string $gender): ?string
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
}
