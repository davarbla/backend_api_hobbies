<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\InstallModel;
use App\Models\FollowModel;

class Follow extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $installModel;
    protected $userModel;
    protected $followModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
        $this->installModel = new InstallModel();
        $this->userModel = new UserModel();
        $this->followModel = new FollowModel();
    }


    public function index()
    {
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
        $dataFollow = $this->followModel->allByLimit($limit, $offset);
        
        $json = array(
            "result" => $dataFollow ,
            "code" => "200",
            "message" => "Success",
        );

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function follow_unfollow()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];

        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }

        $dataUserFrom = $this->userModel->getTokenById($this->postBody['iu']);
        $dataUserTo = $this->userModel->getTokenById($this->postBody['it']);
        $dataSender = $this->userModel->getTokenById($this->postBody['sender']);    
        
        if ($this->postBody['act'] == 'follow') {
            $this->followModel->do_follow($this->postBody);

            $userNotif = $dataSender['id_user'] == $dataUserFrom['id_user'] ? $dataUserFrom: $dataUserTo;
            $userToken = $dataSender['id_user'] == $dataUserFrom['id_user'] ? $dataUserTo : $dataUserFrom;

            //send notif fcm to token user
            $dataFcm = array(
                'title'   => $titleNotif,
                'body'    => $descNotif,
                "image"   => $userNotif['image'],
                'payload' => array(
                    "keyname" => 'new_follower',
                    "image"   => $userNotif['image']
                ),
            );
            
            $this->userModel->sendFCMMessage($userToken['token_fcm'], $dataFcm);
            //send notif fcm to  token user
        }
        else if ($this->postBody['act'] == 'unfollow') {
            $this->followModel->do_unfollow($this->postBody);

            $userNotif = $dataSender['id_user'] == $dataUserFrom['id_user'] ? $dataUserFrom: $dataUserTo;
            $userToken = $dataSender['id_user'] == $dataUserFrom['id_user'] ? $dataUserTo : $dataUserFrom;

            //send notif fcm to token user
            $dataFcm = array(
                'title'   => $titleNotif,
                'body'    => $descNotif,
                "image"   => $userNotif['image'],
                'payload' => array(
                    "keyname" => 'new_follower',
                    "image"   => $userToken['image']
                ),
            );
            $this->userModel->sendFCMMessage($userToken['token_fcm'], $dataFcm);
            //send notif fcm to  token user
        }
        
        $dataFollow = $this->followModel->getAllFollowingByIdUser($this->postBody['iu'], $limit, $offset);
        
        if (count($dataFollow) < 1) {
            $json = array(
                "result" => $dataFollow,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataFollow,
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function get_following()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        $dataFollow = $this->followModel->getAllFollowingByIdUser($this->postBody['iu'], $limit, $offset);
        
        if (count($dataFollow) < 1) {
            $json = array(
                "result" => $dataFollow,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataFollow,
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function get_follower()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        $dataFollow = $this->followModel->getAllFollowerByIdUser($this->postBody['iu'], $limit, $offset);
        
        if (count($dataFollow) < 1) {
            $json = array(
                "result" => $dataFollow,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataFollow,
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
            $dataFollow = $this->installModel->saveUpdate($this->postBody);
            $arr = [$dataFollow];
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