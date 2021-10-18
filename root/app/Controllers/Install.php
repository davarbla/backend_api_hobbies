<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\InstallModel;

class Install extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $installModel;
    protected $userModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
        $this->installModel = new InstallModel();
        $this->userModel = new UserModel();
    }


    public function index()
    {
        
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
            
            //master install
            $dataInstall = $this->installModel->allByLimit($limit, $offset);
            
            $json = array(
                "result" => $dataInstall ,
                "code" => "200",
                "message" => "Success",
            );

            //add the header here
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        else {
            //paramater admin panel
            $ac = $this->request->getVar('ac');

            $data = [
				"menu" => [ 
					"activeInstall" => "1" 
				],
			];
            
            $allDataCateg = $this->installModel->allByLimit(1000, 0);
            $data['result'] = $allDataCateg;
            return view('allinstall_view', $data);
        }
        
    }

    public function getByToken()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $dataInstall = $this->installModel->getByToken($this->postBody['tk']);
        
        if (count($dataInstall) < 1) {
            $json = array(
                "result" => $dataInstall,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataInstall,
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function saveUpdate()
    {   
        $this->postBody = $this->authModel->authHeader($this->request);

        $arr = array();
        
        if ($this->postBody['tk'] != '') {
            $dataInstall = $this->installModel->saveUpdate($this->postBody);
            $arr = [$dataInstall];
        }

        if (count( $arr) < 1) {
            $json = array(
                "result" =>  $arr,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" =>  $arr,
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