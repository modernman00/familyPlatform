<?php

session_start();

class user{

    // protected $username = filter_input(INPUT_POST, 'username');
    // protected $password = filter_input(INPUT_POST, 'password');
    // protected $email = filter_input(INPUT_POST, 'email');
    // protected $fullname = filter_input(INPUT_POST, 'fullname');
    // protected $gender = filter_input(INPUT_POST, 'interests');
    // protected $parent1 = filter_input(INPUT_POST, 'parent1');
    // protected $parent = filter_input(INPUT_POST, 'parent');
    // protected $children = filter_input(INPUT_POST, 'children');
    // protected $_SESSION["user"] = $username;
    // protected $_SESSION["pass"] = $password;
    // protected $_SESSION["email"] = $email;
    // protected $_SESSION["fullname"] = $fullname;
    
    // protected $targetDir = "pictures/"
    // protected $fileName = basename($_FILES["file"]["name"]);)
    // protected $targetFilePath = "pictures/".$fileName;
    // protected $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // protected connection  = mysqli_connect ("localhost", "root", "", "family");

    function login($username, $password){
        if(!empty($username)){

            if(mysqli_connect_error()){
                die('Connect Error ('. mysqli_connect_errno() .') '
                . mysqli_connect_error());
            }

            else {
                 $query mysqli_query( $connection, "SELECT * FROM `logininfo` WHERE username='".$username."' AND password='".$password."'" );

                 if(!$query){
                    die('Error: ' . mysqli_error($connection));
                 }

                 if(mysqli_num_rows($query) > 0){
        
                    header("Location:profilepage.php");
                   
                    
             
                  }else{
                    header("Location:error/incorrectuserandpass.php");
            }
        }
    }

    else{
        header("Location:error/empyuser.php");
  die()
    }


        }


    




    function register($password, $email, $fullname, $username)
    {
    if(!empty($email)){
    if(!empty($password)){
    if(!empty($fullname)){

        if(mysqli_connect_error()){
            die('Connect Error ('. mysqli_connect_errno() .') '
            . mysqli_connect_error());
        }

        else {
             $query mysqli_query( $connection, "SELECT * FROM `logininfo` WHERE username='".$username."' AND password='".$password."'" );

             if(!$query){
                die('Error: ' . mysqli_error($connection));
             }

             if(mysqli_num_rows($query) > 0){

                header("Location:alreadyuser.php");
            
                }else{
            
                  $sql = "INSERT INTO `userinfo` (password,email,fullname,username)
                  values ('$password','$email','$fullname','$username')";
                  if ($conn->query($sql)){
                     echo "New record is inserted sucessfully";
                  
                      header("location: profilepage1.php");
                  }
                  else{
                    echo "Error: ". $sql ." ". $conn->error;
                   }
                
              $conn->close();
            
              }
            
            }
            }
            
            else{
                 header("Location:error/empypass.php");
                
                
              die();
            }
             }
            else{
                 header("Location:error/empyuser.php");
              die();
             }}
            else{
                 header("Location:error/empyuser.php");
              die();}
            
            



    }
    }
    }

    function createPro($targetFilePath,$fileType,$fileName){
        
                    
            if(isset($_POST["submit"]) && !empty($_FILES["file"]["name"])){
                // Allow certain file formats
                $allowTypes = array('jpg','png','jpeg','gif','pdf');
                if(in_array($fileType, $allowTypes)){
                    // Upload file to server
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
                        // Insert image file name into database
                        $insert = $db->query("UPDATE `person` SET profilePic='$targetFilePath' WHERE email='".$_SESSION["email"]."'");

                    if($insert){
                            $statusMsg = "The file ".$fileName. " has been uploaded successfully.";
                        
                        
                        }else{
                            $statusMsg = "File upload failed, please try again.";
                        } 
                    }else{
                        $statusMsg = "Sorry, there was an error uploading your file.";
                    }
                }else{
                    $statusMsg = 'Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed to upload.';
                }
            }else{
                header("Location:empyuser.php");
            }


                $sql = ("UPDATE `person` SET profilePic='.$fileName.' WHERE email='".$_SESSION["email"]."'");



        
    }
}
    
    
}


}


?>