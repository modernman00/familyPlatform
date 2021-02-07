<?php

namespace App\classes;

use App\classes\AllFunctionalities;

class ProcessImg extends AllFunctionalities
{
    public $profileImg;

    public function processProfileImage()
    {
        try {
            if (!$_FILES) {
                throw new \Exception("Error Processing Request - PIC NOT ADDED", 1);
            }
            
            fileUpload("img/profile/", 'profileImage');
            // create a file path name for the database
            $fileName = checkInputImage($_FILES['profileImage']['name']);
            $this->profileImg = $fileName;

            if(!$_SESSION['id']) {
                throw new \Exception("can't find id", 1);      
            }

            $data = [
                'img' => $fileName,
                'id' => $_SESSION['id']
            ];

            $result = $this->insertData_NoRedirect($data, 'profile_pics');
            if(!$result) {
                throw new \Exception("Error Processing PROFILE IMAGE", 1);
                
            }
            return $result;
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
