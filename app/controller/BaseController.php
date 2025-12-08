<?php

declare(strict_types=1);

namespace App\controller;
use Src\Exceptions\NotFoundException;
use Src\Utility;

use App\model\{
    SingleCustomerData,
};
use Src\functionality\{
    SignIn,
};

class BaseController
{
    public static function viewWithCsp($view, $data = [])
    {
        Utility::view($view, $data);
    }

    public static function membersData()
    {
        $id = self::returnId();
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
        return self::formatPerson($memberData);
    }

    public static function returnId()
    {
        $VerifyJWT = SignIn::verify();
        $id = checkInput($VerifyJWT['id']) ?? throw new NotFoundException("Error Request_2");
        if (!isset($_SESSION['id'])) {
            $_SESSION['id'] = $id;
        }
        return $id;
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
            'birthDate' => sprintf('%d-%s-%d', $data['year'] ?? 0, $data['month'] ?? '', $data['day'] ?? 0),
            'maritalStatus' => $data['marital_status'] ?? '',
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'country' => $data['country'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'day' => $data['day'] ?? null,
            'month' => $data['month'] ?? null,
            'year' => $data['year'] ?? null,
            'siblings' => $data['siblings'] ?? null,
            'children' => $data['kids'] ?? null,
            'profilePics' => $data['img'] ?? null,
            'img' => $data['img'] ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex"
        ];
    }

}
