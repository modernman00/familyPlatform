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
                msgException(401, "Error Processing Request - PIC NOT ADDED");
                
            }
        

            fileUpload("public/img/profile/", 'profileImageFile');
            // create a file path name for the database
            $fileName = checkInputImage($_FILES['profileImageFile']['name']);


            $this->profileImg = $fileName;

            if (!$_SESSION['id']) {
                throw new \Exception("can't find id", 1);
            }

            $data = [
                'img' => $fileName,
                'where_from' => 'profile',
                'id' => checkInput($_SESSION['id'])
            ];

        
            $insertFile = new Insert();

            // insert the photo to the images table
            $insertFile->submitForm('images', $data);

            // Update the profile_table
            $this->update('profilePics', 'img', $fileName, 'id', checkInput($_SESSION['id']));

             msgSuccess(200, "Profile image updated");

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * picsSource will either be post or profile or even avatar. it is from the public/img Directory
     */

    public static function showPostImg(string $picsSource)
    {
        //$imgName = checkInput($_GET['pics']);
         $imgName = $_GET['pics'];
        $imgDir = checkInput($_GET['dir']);
        $postSource = checkInput($picsSource);
        return "/$imgDir/$postSource/$imgName";
      
    }
}
