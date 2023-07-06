<?php

namespace App\classes;

use App\classes\AllFunctionalities;
use Exception;

class Sanitise extends AllFunctionalities
{
    /**
     * @var (int|string)[]
     *
     * @psalm-var list<array-key>
     */
    private $key  = array();

    /**
     * @var array
     *
     * @psalm-var list<mixed>
     */
    private array $value = array();
    private $value2 = array();
    public $error = array();

    /**
     * @var array
     */
    private $cleanData = array();

    /**
     * @var int|null
     *
     * @psalm-var 0|null|positive-int
     */
     private $data;


    /**
     * Class constructor.
     *  $data  = [min = [12, 12], max = [12, 12], data = [name, password]]
     * you must firstly check if $this.error is empty before calling $self->getData
     */
    public function __construct(private array $formData, private mixed $dataLength = null)
    {
        try {
            unset($this->formData['submit']);

            // rid it of token
            if (isset($this->formData['token'])) {
                unset($this->formData['token']);
            }

            $this->key = array_keys($this->formData);
            $this->value = array_values($this->formData);
            $this->dataCount = count($this->value);
            // $this->dataLength = $data ?? null;  //code...
        } catch (\Throwable $th) {
            $this->error[] = " Are you human or robot ". $th->getMessage();
        }
    }

    private function emailVal(): void
    {
        if (isset($this->formData['email']) && !filter_var($this->formData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error[] = "Invalid Email Format ";
            // throw new Exception("Error Processing Request - Email format", 1);
            
        }
    }

    private function passVal(): static
    {
        if (isset($this->formData['password']) && isset($this->formData['confirm_password']) && $this->formData['password'] !== $this->formData['confirm_password']) {
                $this->error[] = " Your passwords do not match";
        }
        return $this;
    }


    private function checkEmpty(): static
    {

        for ($x = 0; $x < count($this->key); $x++) {
            if (empty($this->value[$x]) && $this->value[$x] == "" || $this->value[$x] == 'select') {
                $cleanNameKey = strtoupper(preg_replace('/[^0-9A-Za-z@.]/', ' ', $this->key[$x]));
                $this->error[]  = "The $cleanNameKey question is required";
            }
        }
        return $this;
    }

    /**
     * both arrays data must be of the same length
     *   min is the minimum length expected of the post
     *   Max is the minimum length expected of the post
     */
    private function checkLength(): static
    {
        $dataKey = null;
        if ($this->dataLength) {
            for ($x = 0; $x < count($this->dataLength['data']); $x++) {
                $dataKey[] = $this->dataLength['data'][$x];
                $dataPost = $_POST[$this->dataLength['data'][$x]];
                $cleanNameKey = strtoupper(preg_replace('/[^0-9A-Za-z@.]/', ' ', $dataKey[$x]));
                if (strlen($dataPost) < $this->dataLength['min'][$x]) {
                    $this->error[]  = "Your response to '{$cleanNameKey}' question does not meet either the required minimum input limit";
                    
                } elseif (strlen($dataPost) > $this->dataLength['max'][$x]) {
                    $this->error[]  = "Your response to '{$cleanNameKey}' question exceeds the required maximum limit ";
                }
            }
        }
        return $this;
    }

    protected function sanitise(): static
    {
        for ($x = 0; $x < count($this->key); $x++) {
            $this->data = $this->value[$x];
            $this->data = trim($this->value[$x]);
            $this->data = stripslashes($this->value[$x]);
            $this->data = htmlspecialchars($this->value[$x]);
            $this->data = strip_tags($this->value[$x]);
            $this->data = htmlentities($this->value[$x]);
            $this->data = preg_replace("/[^0-9A-Za-z@'._-]/", ' ', $this->value[$x] );
            $this->value2[] = $this->data;
        }
        return $this;
    }

    private function setArrayData(): array
    {
        $options = array('cost' => 10);
        $this->cleanData = array_combine($this->key, $this->value2);
        // if password and confirm password are given, hash password
        // if only password is given, do not hash password

        if (isset($this->cleanData['password']) && isset($this->cleanData['confirm_password'])) {
            $this->cleanData['password'] = password_hash($this->cleanData['password'], PASSWORD_DEFAULT, $options) ?? null;
            unset($this->cleanData['confirm_password']);
        }

        //  return true;
        // if($this->image) {
        // $this->key2['image_path'] = $this->image;
        //  }
        return $this->cleanData;
    }

    private function runFunctions(): void
    {
        $this->emailVal();
        $this->passVal();
        $this->checkEmpty();
        $this->checkLength();
        $this->sanitise();
        $this->setArrayData();
    }


    /**
     * either returns an error which it will echo or the sanitised data in an array
     * @return mixed 
     */

    public function getData(): array
    {
        // try {
            $this->runFunctions();

            return $this->cleanData;
        // } catch (\Throwable $th) {
        //     // showError($th);
        //     $this->error[]  = "The $cleanNameKey question is required\n";
        // } 
    }
}
