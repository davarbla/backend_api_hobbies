<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\PostModel;

use App\Models\CategoryModel;
use App\Models\UserCategoryModel;
use App\Models\LikedModel;

use App\Models\CommentModel;
use App\Models\DownloadModel;

class Post extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $postModel;

    protected $userModel;
    protected $categModel;
    protected $userCategModel;

    protected $likedModel;
    protected $commentModel;
    protected $downloadModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); 
        $this->postModel = new PostModel();
        $this->userModel = new UserModel();

        $this->categModel = new CategoryModel();
        $this->userCategModel = new UserCategoryModel();
        $this->likedModel = new LikedModel();

        $this->commentModel = new CommentModel();
        $this->downloadModel = new DownloadModel();
    }

    public function index() {
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
            
            $dataPost = array();

            $idUser = $this->postBody['iu'];
            $idCateg = $this->postBody['ic'];
            $idPost = $this->postBody['ip'];
            $query = $this->postBody['qy'];

            if ($query != '') {
                $dataPost = $this->postModel->searchAllPosts($query, $idUser, $limit, $offset);
            }
            else if ($idUser != '' && $idCateg != '') {
                $dataPost = $this->postModel->getAllByIdUserCateg($idUser, $idCateg, $limit, $offset);
            }
            else if ($idUser != '' && $idPost != '') {
                $dataPost = $this->postModel->getAllByIdPostIdUser($idPost, $idUser, $limit, $offset);
            }
            else if ($idUser != '') {
                $dataPost = $this->postModel->getAllByIdUser($idUser, $limit, $offset);
            }
            else if ($idCateg != '') {
                $dataPost = $this->postModel->getAllByIdCateg($idCateg, $limit, $offset);
            }
            else if ($idPost != '') {
                $dataPost = $this->postModel->getAllByIdPost($idCateg, $limit, $offset);
            }
            else {
                //master post
                $dataPost = $this->postModel->allByLimit($limit, $offset);
            }

            if (count($dataPost) < 1) {
                $json = array(
                    "result" => $dataPost,
                    "code" => "201",
                    "message" => "Data not found",
                );
            }
            else {
                $json = array(
                    "result" => $dataPost,
                    "code" => "200",
                    "message" => "Success",
                );
            }
            
            //add the header here
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        else {
            $data = [
				"menu" => [ 
					"activePost" => "1" 
				],
			];

            if ($ac == 'reported') {
                $data = [
                    "menu" => [ 
                        "activeReportedPost" => "1" 
                    ],
                ];

                $allData = $this->postModel->allByLimitPanel(1000, 0, '', '1');
                $data['result'] = $allData;
                return view('allpost_view', $data);
            }
            else if ($ac == 'deleted') {
                $data = [
                    "menu" => [ 
                        "activeDeletedPost" => "1" 
                    ],
                ];

                $allData = $this->postModel->allByLimitPanel(1000, 0, '0', '');
                $data['result'] = $allData;
                return view('allpost_view', $data);
            }
            else {
                $allData = $this->postModel->allByLimitPanel(1000, 0);
                $data['result'] = $allData;
			    return view('allpost_view', $data);
            }
        }
    }

    public function latest() {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        $dataPost = array();

        $dataPost = $this->postModel->allByLimitByIdUserCountry($this->postBody['iu'], $limit, $offset, $this->postBody['cc']);

        if (count($dataPost) < 1) {
            $json = array(
                "result" => $dataPost,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataPost,
                "code" => "200",
                "message" => "Success",
            );
        }
        
        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function like_dislike_download()
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
        $idPost = $this->postBody['ip'];
        $titleNotif = $this->postBody['titleNotif'];
        $desc = $this->postBody['descNotif'];

        if ($this->postBody['act'] == 'like') {
            $this->postModel->do_like($this->postBody);
            $this->likedModel->do_liked_post($this->postBody);
           
          //  $this->send_notif_post($idUser, $idPost, true, $titleNotif, $desc);

        }
        else if ($this->postBody['act'] == 'dislike') {
            $this->postModel->do_dislike($this->postBody);
            $this->likedModel->do_liked_post($this->postBody);
        }
        else if ($this->postBody['act'] == 'download') {
            $this->downloadModel->saveUpdate($this->postBody);
            $this->postModel->do_download($this->postBody);
        }
        
        $dataPost = [$this->postModel->getById($idUser)];
        
        if (count($dataPost) < 1) {
            $json = array(
                "result" => $dataPost,
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => $dataPost,
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function comment()
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

        $dataComment = array(); 
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ip'];
        $desc = $this->postBody['ds'];
        $titleNotif = $this->postBody['titleNotif'];
        

        $getUser = $this->userModel->getById($idUser);

        if ($idUser != '' && $idPost != '' && trim($desc) != '' && $getUser['id_user'] != '') {
            $this->send_notif_post($idUser, $idPost, false, $desc, $titleNotif);

            $this->commentModel->do_comment_post($this->postBody);
            $dataComment = $this->commentModel->allByLimitByIdPost($idPost, $limit, $offset);
        }

        $arr = $dataComment; 
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

    public function send_notif_post($idUser, $idPost, $isLiked = true, $comment = '', $titleNotif = '' ) {
        $singlePost = $this->postModel->getById($idPost);

        $idCateg = $singlePost['id_category'];
        $categPost = $this->categModel->getById($idCateg);
        $checkUserCateg = $this->userCategModel->getByUserCateg($singlePost['id_user'], $idCateg);
        
        //send notif FCM to user owner post
        $alreadySent = false;
        if ($singlePost['id_user'] != '' && $idUser != '') {
            $actionUser = $this->userModel->getTokenById($idUser);
            $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
            
            $desc = $singlePost['description'];
            $image = $singlePost['image'];
            if ($titleNotif == ''){
                $titleNotif = $isLiked ? "Post like  " . $actionUser['fullname'] : "Post commented by " . $actionUser['fullname'];
            }
            $descNotif =  $comment != '' ?  $comment : $desc;

            $dataFcm = array(
                'title'   => $titleNotif,
                'body'    => $descNotif . "\n#" . $categPost['title'],
                "image"   => $image,
                'payload' => array(
                    "keyname" => $isLiked ? 'liked_post' : 'commentted_post',
                    "post" => $singlePost,
                    "image"   => $image
                ),
            );

            $this->userModel->sendFCMMessage($ownerUser['token_fcm'], $dataFcm);
            $alreadySent = true;
        }

        //send notif FCM to category subscription
        if ($categPost['subscribe_fcm'] != '' && !$isLiked ) {
            $actionUser = $this->userModel->getTokenById($idUser);
            $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);

            $desc = $singlePost['description'];
            $image = $singlePost['image'];
            if ($titleNotif == ''){
                $titleNotif = $isLiked ? "Post like " . $actionUser['fullname'] : "Post commented by " . $actionUser['fullname'];
            }
            $descNotif =  $comment != '' ?  $comment : $desc;

            $dataFcm = array(
                'title'   => $titleNotif,
                'body'    => $descNotif,
                "image"   => $image,
                'payload' => array(
                    "keyname" => $isLiked ? 'liked_post' : 'commentted_post',
                    "post" => $singlePost,
                    "image"   => $image
                ),
            );
           $this->userModel->sendFCMMessage('/topics/' . $categPost['subscribe_fcm'], $dataFcm);
        }
    }

    public function get_download()
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

        $dataDownload = array(); 
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ip'];
        if ($idPost != '') {
            $dataDownload = $this->downloadModel->allByLimitByIdPost($idPost, $limit, $offset);
        }
        else if ($idUser != '') {
            $dataDownload = $this->downloadModel->allByLimitByIdUser($idUser, $limit, $offset);
        }


        $arr = $dataDownload; 
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

    
    public function get_comment()
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

        $dataComment = array(); 
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ip'];
        if ($idPost != '') {
            $dataComment = $this->commentModel->allByLimitByIdPost($idPost, $limit, $offset);
        }
        else if ($idUser != '') {
            $dataComment = $this->commentModel->allByLimitByIdUser($idUser, $limit, $offset);
        }


        $arr = $dataComment; 
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

    public function get_bycateg()
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
        $idUser = $this->postBody['iu'];
        if ($idUser != '' && $idCateg != '') {
            $dataPosts = $this->postModel->getAllByIdUserCateg($idUser, $idCateg, $limit, $offset);
        }
        else if ($idCateg != '') {
            $dataPosts = $this->postModel->getAllByIdCateg($idCateg, $limit, $offset);
        }
        else if ($idUser != '') {
            $dataPosts = $this->postModel->getAllByIdUser($idUser, $limit, $offset);
        }

        //print_r($dataPost);
        //die();

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

    
    public function get_byid()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $dataPosts = array(); 
        
        $id = $this->postBody['id'];
        if ($id != '') {
            $dataPosts = $this->postModel->getByIdArray($id);
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

    public function update_byid()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $arr = array();
        $idPost = $this->postBody['id'];
        $idUser = $this->postBody['iu'];

        if ($idPost != '') {
            
            $dataPost = $this->postModel->getById($idPost);

            $action = $this->postBody['act'];
            if ($action == 'update_status') {
                $st = $this->postBody['st'];
                $data = [
                    'id_post' => $idPost,
                    'status'  => $st ,
                ];
                $this->postModel->save($data);

                $this->update_bystatus($idUser, $idPost, $st);
            }
            else if ($action == 'update_view') {
                $data = [
                    'id_post'     => $idPost,
                    'total_view'  => $dataPost['total_view'] + 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'update_download') {
                $data = [
                    'id_post'     => $idPost,
                    'total_download'  => $dataPost['total_download'] + 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'update_comment') {
                $data = [
                    'id_post'     => $idPost,
                    'total_comment'  => $dataPost['total_comment'] + 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'delete_comment') {
                $data = [
                    'id_post'     => $idPost,
                    'delete_comment'  => $dataPost['delete_comment'] - 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'update_like') {
                $data = [
                    'id_post'     => $idPost,
                    'total_like'  => $dataPost['total_like'] + 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'delete_like') {
                $data = [
                    'id_post'     => $idPost,
                    'total_like'  => $dataPost['total_like'] - 1,
                ];
                $this->postModel->save($data);
            }
            else if ($action == 'update_user') {
                $data = [
                    'id_post'     => $idPost,
                    'total_user'  => $dataPost['total_user'] + 1,
                ];
                $this->postModel->save($data);
            } 
            else if ($action == 'update_report') {
                $data = [
                    'id_post'     => $idPost,
                    'total_report'  => $dataPost['total_report'] + 1,
                ];
                $this->postModel->save($data);
            }

            $arr = [$dataPosts]; 
        }
        
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

    public function update_bystatus($idUser, $idPost, $status='1') {
        $dataPost = $this->postModel->getById($idPost);

        if ($status == '0') {
            $dataUser = $this->userModel->getById($idUser);
            // update total_post user + 1
            $data = [
                'id_user'     => $idUser,
                'total_post'  => $dataUser['total_post'] - 1,
            ];
            $this->userModel->save($data);
            
            $idCateg = $dataPost['id_category'];
            $dataCateg = $this->categModel->getById($idCateg);
            
            // update total_post category + 1
            $data2 = [
                'id_category'     => $idCateg,
                'total_post'  => $dataCateg['total_post'] - 1,
                'total_interest'  => $dataCateg['total_interest'] - 1,
            ];
            $this->categModel->save($data2);

            $dataUserCateg = $this->userCategModel->getByUserCateg($idUser, $idCateg);
            $data3 = [
                'id_category'     => $idCateg,
                'id_user'         => $idUser,
                'count_post'      => 1,
                'count_interest'  => 1,
            ];
            
            if ($dataUserCateg['id_user_category'] != '') {
                // update total_post user_category + 1
                $data3 = [
                    'id_user_category'     => $dataUserCateg['id_user_category'],
                    'count_post'  => $dataUserCateg['count_post'] - 1,
                    'count_interest'  => $dataUserCateg['count_interest'] - 1,
                ];
            }
            
            $this->userCategModel->save($data3);
        }
        else if ($status == '1') {
            $dataUser = $this->userModel->getById($idUser);
            // update total_post user + 1
            $data = [
                'id_user'     => $idUser,
                'total_post'  => $dataUser['total_post'] + 1,
            ];
            $this->userModel->save($data);
            
            $idCateg = $dataPost['id_category'];
            $dataCateg = $this->categModel->getById($idCateg);
            
            // update total_post category + 1
            $data2 = [
                'id_category'     => $idCateg,
                'total_post'  => $dataCateg['total_post'] + 1,
                'total_interest'  => $dataCateg['total_interest'] + 1,
            ];
            $this->categModel->save($data2);

            $dataUserCateg = $this->userCategModel->getByUserCateg($idUser, $idCateg);
            $data3 = [
                'id_category'     => $idCateg,
                'id_user'         => $idUser,
                'count_post'      => 1,
                'count_interest'  => 1,
            ];
            
            if ($dataUserCateg['id_user_category'] != '') {
                // update total_post user_category + 1
                $data3 = [
                    'id_user_category'     => $dataUserCateg['id_user_category'],
                    'count_post'  => $dataUserCateg['count_post'] + 1,
                    'count_interest'  => $dataUserCateg['count_interest'] + 1,
                ];
            }
            
            $this->userCategModel->save($data3);
        }
    }

}