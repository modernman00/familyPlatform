<?php

namespace App\classes;

use App\classes\AllFunctionalities;

class ProcessImg extends AllFunctionalities
{

    public function processProfileImage()
    {
        try {
            if (!$_FILES) {
                throw new \Exception("Error Processing Request - PIC NOT ADDED", 1);
            }

            fileUpload("img/profile/", 'profileImage');
            // create a file path name for the database

            $data = [
                'img' => $_FILES['profileImage']['name'],
                'id' => $_SESSION['id']
            ];

            $result = $this->insertData_NoRedirect($data, 'profile_pics');
            if(!$result) {
                throw new \Exception("Error Processing PROFILE IMAGE", 1);
                
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
