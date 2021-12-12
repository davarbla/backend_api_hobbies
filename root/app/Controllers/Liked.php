<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\InstallModel;
use App\Models\LikedModel;
use App\Models\CategoryModel;

class Liked extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $installModel;
    protected $userModel;
    protected $likedModel;
    protected $categModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
        $this->installModel = new InstallModel();
        $this->userModel = new UserModel();
        $this->likedModel = new LikedModel();
        $this->categModel = new CategoryModel();
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
        
        //master 
        $dataLiked = $this->likedModel->allByLimit($limit, $offset);
        
        $json = array(
            "result" => $dataLiked ,
            "code" => "200",
            "message" => "Success",
        );

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function like_dislike_categ() {
        $this->postBody = $this->authModel->authHeader($this->request);
        $array = $this->postBody;
        
        $result = array();

        if ($array['iu'] != '' && $array['ic'] != '') {
            $idUser = $array['iu'];
            $idCateg = $array['ic'];
            
            $dataLiked = $this->likedModel->do_liked_category($array);
            $check1 = $this->likedModel->getByIdUserCateg($idUser, $idCateg);
            
            if ($check1['id_liked'] != '' && $check1['is_liked'] == '1') {
                $this->send_notif_categ($idUser, $idCateg);
            }

            $result = [$dataLiked];
        }

       
        $json = array(
            "result" => $result ,
            "code" => "200",
            "message" => "Success",
        );

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function get_post_user()
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

        $dataPosts = array(); 
        
        $idCateg = $this->postBody['ic'];
        if ($idCateg != '') {
            $dataPosts = $this->likedModel->getAllByIdCateg($idCateg, $limit, $offset);
        }

        $idUser = $this->postBody['iu'];
        if ($idUser != '') {
            $dataPosts = $this->likedModel->getAllByIdUser($idUser, $limit, $offset);
        }

        $arr = $dataPosts; 
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

    public function get_by_user()
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
        
        $idUser = $this->postBody['iu'];

        //master 
        $dataLiked = array();

        if ($idUser != '') {
            $dataLiked = $this->likedModel->allByLimitByIdUser($idUser, $limit, $offset);
        }
        
        $arr = $dataLiked;
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

    public function get_by_categ()
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
        
        $idCategory = $this->postBody['ic'];

        //master 
        $dataLiked = array();

        if ($idCategory != '') {
            $dataLiked = $this->likedModel->allByLimitByIdCateg($idCategory, $limit, $offset);
        }
        
        $arr = $dataLiked;
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

    public function send_notif_categ($idUser, $idCateg) {
        $categPost = $this->categModel->getById($idCateg);
        $actionUser = $this->userModel->getTokenById($idUser);
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
            
        $desc = $categPost['description'];
        $image = $categPost['image'];
        if ($titleNotif == ''){
            $titleNotif = "Category liked by " . $actionUser['fullname'];
        }
        if ($descNotif == ''){
            $descNotif =  $desc;
        }

        $dataFcm = array(
            'title'   => $titleNotif,
            'body'    => $descNotif . "\n#" . $categPost['title'],
            "image"   => $image,
            'payload' => array(
                "keyname" => 'liked_categ',
                "categ" => $categPost,
                "image"   => $image
            ),
        );

        
        //send notif FCM to category subscription
        if ($categPost['subscribe_fcm'] != '') {
            $res = $this->userModel->sendFCMMessage('/topics/' . $categPost['subscribe_fcm'], $dataFcm);
        }

    }
    
}