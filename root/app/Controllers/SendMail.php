<?php 
namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\InstallModel;
use App\Models\UserModel;


class SendMail extends BaseController
{
    protected $postBody; 
    protected $authModel;
    protected $installModel;
    protected $userModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel();
        $this->installModel = new InstallModel();
        $this->userModel = new UserModel();
    }

    public function index() 
    {
        $this->postBody = $this->authModel->authHeader($this->request);
    }

    function testMail() { 
        $this->postBody = $this->authModel->authHeader($this->request);

        $to = 'erhacorpdotcom@gmail.com';
        $subject = 'Forgot Password'; 
        $message = 'Test Body Message, How to Reset Password<br/>\n<br/><strong>Forgot Password</strong>'; 
        
        $email = \Config\Services::email();

        $email->setTo($to); //$to);
        $email->setFrom('noreply@in-news.id', 'NoReply Hobbies');
        
        $email->setSubject($subject);
        $email->setMessage($message);

        if ($email->send()) 
		{
            echo 'Email successfully sent';
        } 
		else 
		{
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }
    }

    function sendMail() { 
        $this->postBody = $this->authModel->authHeader($this->request);

        $arr = array();
        $to = $this->postBody['em'];

        if ($to != '') {

            //check email
            $checkExist = $this->userModel->getByEmail($to);
            $checkInstall = $this->installModel->getById($checkExist['id_install']);

            if ($checkInstall['id_install'] != '' && $checkExist['id_user'] != '') {
                $rand = rand(111111, 999999);
                $subject = 'Reset Password'; 
                $message = 'Code verify for reset password.<br/><br/><strong>'.$rand.'</strong><br/><br/>Best Regards, <br/>Hobbies Apps'; 
                
                $data = [
                    "id_install" => $checkInstall['id_install'],
                    "token_forgot" =>  $rand
                ];

                $this->installModel->save($data);
                
                $email = \Config\Services::email();

                $email->setTo($to); 
                $email->setFrom('noreply@in-news.id', 'NoReply Hobbies');
                
                $email->setSubject($subject);
                $email->setMessage($message);

                if ($email->send()) 
                {
                    $arr = $this->userModel->getByUserAll($checkExist['id_user']);
                    
                } 
                else 
                {
                    $data = $email->printDebugger(['headers']);
                    print_r($data);
                }
            }
        }

        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Data email not found, required parameter",
            );
        }
        else {
            $json = array(
                "result" => $arr,
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

}