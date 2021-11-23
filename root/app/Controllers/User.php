<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\InstallModel;
use App\Models\FeedbackModel;

class User extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $installModel;
    protected $userModel;

    protected $feedbackModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
        $this->installModel = new InstallModel();
        $this->userModel = new UserModel();
        $this->feedbackModel = new FeedbackModel();
    }


    public function index()
    {
        $ac = $this->request->getVar('ac');

        $this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);

        if (!$check) {
            $this->postBody = $this->authModel->authHeader($this->request);
            
            $offset = 0;
            $limit = 10;

            $getLimit = $this->request->getVar('lt');
            if ($getLimit != '') {
                $exp = explode(",", $getLimit);
                $offset = (int) $exp[0];
                $limit = (int) $exp[1];
                
            }
            
            //master 
            $dataUser = $this->userModel->allByLimit($limit, $offset);
            
            $json = array(
                "result" => $dataUser ,
                "code" => "200",
                "message" => "Success",
            );

            //add the header here
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        else {
            $data = [
				"menu" => [ 
					"activeUser" => "1" 
				],
			];
            
            $allData = $this->userModel->allByLimitPanel(1000, 0);
            $data['result'] = $allData;
            return view('alluser_view', $data);
        }
    }

    public function confirm_register()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        $arr = array();

        if ($this->postBody['ps'] != '' && $this->postBody['em'] != '') {
            
            $checkExist = $this->userModel->getByEmail($this->postBody['em']);

            if ($checkExist['id_user'] == '') {
                $this->postBody['ps'] = $this->generatePassword($this->postBody['ps']);
                
            }
            else {
                $this->postBody['id'] = $checkExist['id_user'];
                $this->postBody['us']  = $checkExist['username'];
                $this->postBody['img']  = $checkExist['image'];
                $this->postBody['ps']  = $checkExist['password_user'];
            }

            $dataUser = $this->userModel->registerByPhone($this->postBody);
            $arr = [$dataUser]; 
        }

        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Email/Username already exist",
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

    public function feedback()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $dataPush = $this->feedbackModel->do_feedback($this->postBody);
        
        $arr = $dataPush;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Data not found",
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

    public function push_fcm()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $dataPush = $this->userModel->sendFCMMessage($this->postBody['token'], $this->postBody['data']);
        
        $arr = $dataPush;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Data not found",
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

    private function generatePassword($password) {
        return md5(sha1(hash("sha256", $password)));
    }
    
}