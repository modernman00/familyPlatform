<?php

namespace App\controller;
use App\classes\{
    Sanitise, Insert, CheckToken as Token, 
    Transaction as Transaction
};
use Exception;

class Register extends Insert 
{
    private $cleanData;
    private $table = ['personal', 'work', 'contact', 'interest', 'account'];

    public function index()
    {
        view('registration/register');
    }

/**
 * 
 * @param mixed $array this is the POST Data 
 * @param mixed $table database table
 * @param mixed|null $data check len (max and min)
 * @return void 
 */

    public function processForm() 
    {
        printArr($_POST);
        try {
          
          // GENERATE ID
            $id = $_POST['firstName'];
            $id .= rand(100,900);

            $_POST['id'] = $id;    // manually creata  POST element
            $array = $_POST;
             //    TODO log the error and send to developer
            if(!$_POST['id']) throw new Exception('Id cannot be generated');
 
        $data = [
            'min' => [2, 2, 0, 2, 2, 2, 2, 2, 2,2,2, 2, 2, 2,2,2,2,2,2],
            'max' => [15, 15, 15, 15, 30, 30, 15, 50, 10,15,15, 13, 45, 25,30,40,30,20, 30 ],
            'data'=> ['firstName','lastName','alias','spouse', 'fatherName', 'motherName', 'motherMaiden','address','postcode','region','country','mobile','email','favSport','footballTeam','passion','jobTitle','occupation','employerName',]
        ];

           // sanitise
        //    TODO log the error and send to developer
        $sanitise = new Sanitise($array, $data);

        if(!$sanitise) throw new Exception('Not able to review your input. We are currently looking into it');

        $this->cleanData = $sanitise->getData();

        if($this->cleanData === "Error") {
            $errors = $sanitise->getErr();
            return view('error/form', compact('errors'));
        }

        $tableData = [
            [
                'firstName' => $this->cleanData['firstName'],
                'lastName' => $this->cleanData['lastName'],
                 'alias' => $this->cleanData['alias'],
                'spouse' => $this->cleanData['spouse'],
                'fatherName' => $this->cleanData['fatherName'],
                'motherName' => $this->cleanData['motherName'],
                 'motherMaiden' => $this->cleanData['motherMaiden'],
                'birthDate' => $this->cleanData['birthDate'],
                'kids' => $this->cleanData['kids'],
                'gender' => $this->cleanData['gender'],
                'noSiblings' => $this->cleanData['noSiblings'],
                'id' => $this->cleanData['id'],
            ],
            [
                'employmentStatus' => $this->cleanData['employmentStatus'],
                 'jobTitle' => $this->cleanData['jobTitle'],
                'occupation' => $this->cleanData['occupation'],
                'employerName' => $this->cleanData['employerName'],
                'workEmail' => $this->cleanData['workEmail'],
               
                'id' => $this->cleanData['id']      
            ],
             [
                 'address' => $this->cleanData['address'],
                'postcode' => $this->cleanData['postcode'],
                'email' => $this->cleanData['email'],
                'region' => $this->cleanData['region'],
                'country' => $this->cleanData['country'],
                'mobile' => $this->cleanData['mobile'],
                'id' => $this->cleanData['id'],     
            ],
              [
                 'favSport' => $this->cleanData['favSport'],
                'footballTeam' => $this->cleanData['footballTeam'],
                'passion' => $this->cleanData['passion'],               
                'id' => $this->cleanData['id'],     
            ],
             [
                 'password' => $this->cleanData['password'],
                'secretWord' => $this->cleanData['secretWord'], 
                'status' => 'new',            
                'id' => $this->cleanData['id'],     
            ]
        ];

  
        // create session 
        $_SESSION['id'] = $this->cleanData['id'];
         $_SESSION['firstName'] = $this->cleanData['firstName'];
        // submit using function from insert

        $countTable = count($this->table);
        for($i=0; $i < $countTable; $i++) {
      
                $this->submitForm($this->table[$i], $tableData[$i]);
        }

        return view('registration/nextStep');

        } catch (\Throwable $th) {
            echo $th->getMessage();            
        } catch ( Exception $e) {
            echo $e->getMessage();
        }
    
    }
}