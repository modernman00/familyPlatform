<?php

declare(strict_types=1);

namespace App\Controller\members;

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
    public function index(): void
    {
        try {
            // Verify token
            $VerifyJWT = SignIn::verify();

            if (!$VerifyJWT) {
                redirect('/login');
            }

            // Sanitize input
            $id = checkInput($_GET['id'] ?? '');
            if (empty($id)) {
                throw new NotFoundException('Main person ID required');
            }

            // Validate family code from session
            $famCode = $_SESSION['famCode'] ?? null; // From Login.php
            if (!$famCode) {
                throw new NotFoundException('Unauthorized: Family code required');
            }

            // Fetch main person's data
            $tables = ['personal', 'profilePics', 'contact', 'work'];
            $mainPerson = $this->getCustomerData($id, $tables);
            if (empty($mainPerson)) {
                throw new NotFoundException('Main person not found');
            }

            // Format main person data
            $mainPersonData = $this->formatPerson($mainPerson);

            // Fetch other family members
            $spouse = $this->fetchRelationsData($id, 'otherFamily', 'spouse');
            $father = $this->fetchRelationsData($id, 'otherFamily', 'father');
            $mother = $this->fetchRelationsData($id, 'otherFamily', 'mother');
            $siblings = $this->fetchRelationsData($id, 'sibling', 'sibling');
            $children = $this->fetchRelationsData($id, 'children', 'children');

            // Fetch sibling children
            $siblingChildren = $this->getSiblingChildren($siblings);

            // Build response
            $data = [
                'main' => $mainPersonData,
                'spouse' => $spouse[0],
                'father' => $father[0],
                'mother' => $mother[0],
                'siblings' => $siblings,
                'children' => $children,
                'sibling_children' => $siblingChildren
            ];

    //    \printArr($data);

            view('member/organogram', compact('data'));

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
                        $fullData = $this->getCustomerData($getData['id'], ['personal', 'profilePics', 'contact', 'work']);

                        // set relationship for children and siblings
                        if (isset($fullData['gender'])) {
                            $fullData['relationship'] = $this->resolveRelationship($who, $fullData['gender']);
                        }

                        $formatedData = $this->formatPerson($fullData);
                        array_push($data, $formatedData);
                    }
                } else {
                    $relationship = $fullData['relationship'] = $this->resolveRelationship($who, $row["{$who}_gender"]);
                     $sex = $data['children_gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';
                    array_push($data, [
                        'name' => $row["{$who}_name"],
                        'email' => $row["{$who}_email"],
                        'mobile' => $row["{$who}_mobile"],
                        'linked' => $row["{$who}_linked"],
                        'relationship' => $relationship,
                        'gender' => $row["{$who}_gender"],
                        'img' => $data['img'] ? "public/img/profile/{$data['img']}" : "public/img/profile/$sex"
                    ]);
                }
            }
        }

        return $data;
    }



    /**
     * Format main person's data from multiple tables
     * @param array $data
     * @return array
     */
    private function formatPerson(array $data): array
    {
        $sex = $data['gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';

        return [
            'id' => $data['id'] ?? '',
            'firstName' => $data['firstName'] ?? '',
            'lastName' => $data['lastName'] ?? '',
            'name' => $data['firstName'] . ' ' . $data['lastName'] ?? '',
            'famCode' => $data['famCode'] ?? '',
            'gender' => $data['gender'] ?? '',
            'birthDate' => sprintf('%d-%s-%d', $data['year'] ?? 0, $data['month'] ?? '', $data['day'] ?? 0),
            'maritalStatus' => $data['marital_status'] ?? '',
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'father_id' => $data['father_id'] ?? null,
            'relationship' => $data['relationship'] ?? null,
            'country' => $data['country'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'img' => $data['img'] ? "public/img/profile/{$data['img']}" : "public/img/profile/$sex"
        ];
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
                            $fullData = $this->getCustomerData($getData['id'], ['personal', 'profilePics', 'contact', 'work']);


                            // set relationship for children and siblings
                            if (isset($fullData['gender'])) {
                                $fullData['relationship'] = $this->resolveRelationship('sibling_children', $fullData['gender']);
                                $fullData['father_id'] = $id;
                            }
                  
                        }


                        $formatedData = $this->formatPerson($fullData);

                        array_push($siblingChildren, $formatedData);
                    } else {
                         $sex = $data['children_gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';
                        array_push($siblingChildren, [
                            'father_id' => $id,
                            'name' => $data['children_name'],
                            'email' => $data['children_email'],
                            'gender' => $data['children_gender'],
                            'relationship' => $this->resolveRelationship('sibling_children', $data['children_gender']),
                            // 'mobile' => $data['children_mobile'],
                            'linked' => $data['children_linked'],
                            'img' => $data['img'] ? "public/img/profile/{$data['img']}" : "public/img/profile/$sex"
                        ]);
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
