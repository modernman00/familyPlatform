<?php

namespace App\controller;
use App\classes\{
    Sanitise, Insert
};
use Exception;

class Register extends Insert 
{
    private $cleanData;
    private $table = ['personal', 'work', 'contact'];

    public function index()
    {
        view('register');
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
        try {
          
          // GENERATE ID
            $id = $_POST['firstName'];
            $id .= rand(100,900);

            $_POST['id'] = $id;    // manually creata  POST element
            $array = $_POST;
             //    TODO log the error and send to developer
            if(!$_POST['id']) throw new Exception('Id cannot be generated');
 
        $data = [
            'min' => [10, 4, 4, 4],
            'max' => [14, 30, 20, 20],
            'data'=> ['phoneNumber', 'jobTitle', 'firstName', 'lastName']
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
                'fatherName' => $this->cleanData['fatherName'],
                'motherName' => $this->cleanData['motherName'],
                'birthDate' => $this->cleanData['birthDate'],
                'noKids' => $this->cleanData['noKids'],
                'noSiblings' => $this->cleanData['noSiblings'],
                'id' => $this->cleanData['id'],
            ],
            [
                 'jobTitle' => $this->cleanData['jobTitle'],
                'occupation' => $this->cleanData['occupation'],
                'employerName' => $this->cleanData['employerName'],
                'workEmail' => $this->cleanData['workEmail'],
                'employers' => $this->cleanData['employer'], 
                'id' => $this->cleanData['id']      
            ],
             [
                 'address' => $this->cleanData['address'],
                'postcode' => $this->cleanData['postcode'],
                'email' => $this->cleanData['email'],
                'region' => $this->cleanData['region'],
                'phoneNumber' => $this->cleanData['phoneNumber'],
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