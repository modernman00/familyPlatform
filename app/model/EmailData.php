<?php 

namespace App\model;

class EmailData 
{
       private $username;
        private $password;
        private $senderEmail;
        private $senderName;
    /**
     * 
     * @param string $sender must be either, self, customer, payment, referral
     * @return void 
     */
    function __construct(string $sender)
    {    
        if($sender === 'customer') {
            $this->username = getenv("APP_USERNAME");
            $this->password = getenv("APP_PASSWORD");
            $this->senderName = getenv('APP_SENDER');
            $this->senderEmail = getenv("APP_EMAIL");

        } elseif ( $sender === 'payment') {

            $this->username = getenv("PAY_USERNAME");
            $this->password = getenv("PAY_PASSWORD");
            $this->senderName = getenv('PAY_SENDER');
            $this->senderEmail = getenv("PAY_EMAIL");

        } elseif ($sender === 'referral') {

            $this->username = getenv("REFER_USERNAME");
            $this->password = getenv("REFER_PASSWORD");
            $this->senderName = getenv('REFER_SENDER');
            $this->senderEmail = getenv("REFER_EMAIL");
        }
      
            $this->username = getenv("APP_USERNAME");
            $this->password = getenv("APP_PASSWORD");
            $this->senderName = getenv('APP_SENDER');
            $this->senderEmail = getenv("APP_EMAIL");            
       
    }

    private function setEmailData()
    {
            define ('USER_APP', $this->username);
            define ('PASS', $this->password);
            define ('APP_EMAIL', $this->senderEmail);
            define ('APP_NAME', $this->senderName);
    }

    function getEmailData()
    {
        return $this->setEmailData();
    }

}