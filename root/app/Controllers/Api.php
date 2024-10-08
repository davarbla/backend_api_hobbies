<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\UserCategoryModel;
use App\Models\UserPostModel;
use App\Models\UserGalleryModel;
use App\Models\AuthHeaderModel;

use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\FollowModel;

use App\Models\LikedModel;
use App\Models\CommentModel;
use App\Models\DownloadModel;

class Api extends BaseController
{
	protected $postBody; 
    protected $authModel;
    protected $categModel;
    
    
    protected $postModel;
    protected $userCategModel;
    protected $userPostModel;
    protected $userGalleryModel;
    protected $userModel;

    protected $followModel;
    protected $likedModel;
    protected $commentModel;

    protected $downloadModel;

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
        $this->categModel = new CategoryModel();
                
        $this->userCategModel = new UserCategoryModel();
        $this->userPostModel = new UserPostModel();
        $this->userGalleryModel = new UserGalleryModel();

        $this->postModel = new PostModel();    
        $this->userModel = new UserModel();
        $this->followModel = new FollowModel();

        $this->likedModel = new LikedModel();
        $this->commentModel = new CommentModel();
        $this->downloadModel = new DownloadModel();
    }


    public function index()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');//MyTheme.pageLimit
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        $country = $this->postBody['cc'];
        $latitude =  $this->postBody['lat'];
        $splitLat = explode(",",$latitude);
        $lat = $splitLat[0];
        $lng = $splitLat[1];
        
        $miles = 1000/1.6; //Meters/1.6
        
        //ALL Category (groups)
        $dataCateg = $this->categModel->allByLimitCountry($limit, $offset, $country);
        
        $idUser = $this->postBody['iu'];
        //MY categories
        $dataUserCateg = $this->userCategModel->categUserByLimit($idUser , $limit, $offset);
        //print_r($dataUserCateg);

        //MY posts interactions
        $dataUserPost = $this->userPostModel->categUserByLimit($idUser , $limit, $offset);

        //MY users photo galleries
        $dataUserGallery = $this->userGalleryModel->categUserByLimit($idUser , $limit, $offset);

        //My posts (Owner)
        $dataMyPost = $this->postModel->getAllByIdUser($idUser, $limit, $offset);

        //ALL post (latest)
        $dataLatestPost = $this->postModel->allByLimitByIdUserCountry($idUser, $limit, $offset, $country);

        //ALL users
        $dataUser = $this->userModel->allByLimitCountryDistance($limit, $offset, $country,  $lng, $lat, $miles);

        //MY following 
        $dataFollowing = $this->followModel->getAllFollowingByIdUser($this->postBody['iu'], $limit, $offset);

        //MY followers 
        $dataFollower = $this->followModel->getAllFollowerByIdUser($this->postBody['iu'], $limit, $offset);
        
        $results = array();
        $results['category'] = $dataCateg;  
        $results['mycategory'] = $dataUserCateg;  
        $results['myuserpost'] = $dataUserPost;  
        $results['myusergallery'] = $dataUserGallery;  
        $results['mypost'] = $dataMyPost;  
        $results['latest_post'] = $dataLatestPost;  
        $results['all_user'] = $dataUser;  
        $results['following'] = $dataFollowing;  
        $results['follower'] = $dataFollower;  

        //get all liked by iduser 
        $dataLiked = $this->likedModel->allByLimitByIdUser($this->postBody['iu'], $limit, $offset);
        $results['liked'] = $dataLiked; 
        
        //get all liked by iduser 
        $dataDownloaded = $this->downloadModel->allByLimitByIdUser($this->postBody['iu'], $limit, $offset);
        $results['downloaded'] = $dataDownloaded; 

        //get all commment by iduser 
        $dataComment = $this->commentModel->allByLimitByIdUser($this->postBody['iu'], $limit, $offset);
        $results['comment'] = $dataComment;  

        $json = array(
            "result" => $results,
            "code" => "200",
            "message" => "Success",
        );

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function send_fcm() {
        $this->postBody = $this->authModel->authHeader($this->request);
         //test send fcm message
         $results = array();
         if ($this->postBody['token'] != '') {
            $results = $this->userModel->sendFCMMessage($this->postBody['token'], $this->postBody['data']);
        }

        $arr = $results;

        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required paramater",
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

    public function users()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $offset = 0;
        $limit = 10000;
        

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }

        $arr = array();
        
        $idUser = $this->postBody['iu'];
        if ($idUser != '') {
            $getUserById = $this->userModel->getById($idUser);
            if ($getUserById['id_user'] != '') {
                $arr = [$getUserById];
            }
        }
        else {
            $country = $this->postBody['cc'];
            $latitude =  $this->postBody['lat'];
            $splitLat = explode(",",$latitude);
            $lat = $splitLat[0];
            $lng = $splitLat[1];
            $miles =  $this->postBody['miles'];
            if ($miles == '') {
              $miles = 100; // Meters/1.6 (160 KM)
            }
            $arr = $this->userModel->allByLimitCountryDistance($limit, $offset, $country,  $lng, $lat, $miles);
        }
        
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "No users around found",
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

    public function usercateg()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $dataCateg = $this->userCategModel->saveChoice($this->postBody);

        $arr = $dataCateg; 
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

    public function get_user()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        if ($this->postBody['is'] != '' &&  $this->postBody['iu'] != '') {
            $this->postBody['id'] = $this->postBody['iu'];
            $this->userModel->updateUser($this->postBody);
        }
        
        $dataUser = $this->userModel->getById($this->postBody['iu']);

        if ($dataUser['id_user'] == '') {
            $json = array(
                "result" => array(),
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => [$dataUser],
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }
    //idem get_user()
    public function delete_user_msg()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        if ($this->postBody['is'] != '' &&  $this->postBody['iu'] != '') {
            $this->postBody['id'] = $this->postBody['iu'];
            $this->userModel->deleteUserMessage($this->postBody);
        }
        
        $dataUser = $this->userModel->getById($this->postBody['iu']);

        if ($dataUser['id_user'] == '') {
            $json = array(
                "result" => array(),
                "code" => "201",
                "message" => "Data not found",
            );
        }
        else {
            $json = array(
                "result" => [$dataUser],
                "code" => "200",
                "message" => "Success",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function update_user_byid()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $arr = array();
        $idUser = $this->postBody['iu'];

        $message = "Data not found";

        if ($idUser != '') {
            
            $dataUser = $this->userModel->getById($idUser);
            $arr = [$dataUser]; 
            
            $action = $this->postBody['act'];
            if ($action == 'update_about' && $this->postBody['ab'] != '') {
                $data = [
                    'id_user'     => $idUser,
                    'about'  => $this->postBody['ab'],
                    'latitude'  => $this->postBody['lat'],
                    'location'  => $this->postBody['loc'],
                ];
                $this->userModel->save($data);
            }
            else if ($action == 'update_phone' && $this->postBody['ph'] != '') {
                $data = [
                    'id_user'     => $idUser,
                    'phone'  => $this->postBody['ph'],
                    'latitude'  => $this->postBody['lat'],
                    'location'  => $this->postBody['loc'],
                ];
                $this->userModel->save($data);
            }
            else if ($action == 'update_about_fullname' && $this->postBody['ab'] != '' && $this->postBody['fn'] != '') {
                $data = [
                    'id_user'     => $idUser,
                    'fullname'  => $this->postBody['fn'],
                    'about'  => $this->postBody['ab'],
                    'latitude'  => $this->postBody['lat'],
                    'location'  => $this->postBody['loc'],
                    'height'  => $this->postBody['height'],
                    'weight'  => $this->postBody['weight'],
                    'age'  => $this->postBody['age'],
                    'position'  => $this->postBody['position'],
                    'protection'  => $this->postBody['protection'],
                    'relationship'  => $this->postBody['relationship'],
                    'bodyColor'  => $this->postBody['bodyColor'],
                    'bodyShape'  => $this->postBody['bodyShape'],
                    'hair'  => $this->postBody['hair'],
                    'publish'  => $this->postBody['publish'],
                    'status'  => $this->postBody['status'],

                    
                ];
                $this->userModel->save($data);
            }
            else if ($action == 'update_location' && $this->postBody['lat'] != '') {
                $data = [
                    'id_user'     => $idUser,
                    'location'  => $this->postBody['loc'],
                    'latitude'  => $this->postBody['lat'],
                    'location'  => $this->postBody['loc'],
                ];
                $this->userModel->save($data);
            } 
            else if ($action == 'change_password') {
                if ($this->postBody['ps'] != '' && $this->postBody['np'] != '') {
                    $oldpasswrd = $this->generatePassword($this->postBody['ps']);
                    $newpasswrd = $this->generatePassword($this->postBody['np']);
                    if ($oldpasswrd == $dataUser['password_user']) {
                        $data = [
                            'id_user'     => $idUser,
                            'password_user' => $newpasswrd,
                            'location'  => $this->postBody['loc'],
                            'latitude'  => $this->postBody['lat'],
                            'location'  => $this->postBody['loc'],
                        ];
                        $this->userModel->save($data);
                    }
                    else {
                        $arr = array();
                        $message = "Old Password invalid...";
                    }
                }
                else {
                    $arr = array();
                    $message = "Data parameter required...";
                }
            }

            
        }
        
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => $message,
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

    public function category()
    {
        //$authModel = new AuthHeaderModel();
        $this->postBody = $this->authModel->authHeader($this->request);
        
        //print_r($this->postBody);
        //die();
        // limit 0, 4 ==> limit 4, offset 0
        // limit 4, 8 ===> limit 4, offset 4,

        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        $dataCateg = $this->categModel->allByLimit($limit, $offset);
        
        $arr = $dataCateg; //array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 5, 'date' => date('Y-m-d H:i:s'),);
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Data no found",
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

    public function join_unjoin_category()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        
        $idUser = $this->postBody['iu'];
        $idCateg = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $desc = $this->postBody['descNotif'];
        $groupPrivate = $this->postBody['groupPrivate'];
        $isJoinedstr = $this->postBody['isJoined'];
        $isJoined = false;
        if ($isJoinedstr == '1') {
               $isJoined = true;
        }

        $dataCateg = array();

        if ($idUser != '' && $idCateg != '') {
            if ($groupPrivate == '1' ){
                $dataCateg = [$this->userCategModel->join_unjoin_private($this->postBody)];
            }else{
                $dataCateg = [$this->userCategModel->join_unjoin($this->postBody)];
            }
            
            $masterCateg = $this->categModel->getById($idCateg);
            $checkExist = $this->userCategModel->getByUserCateg($idUser, $idCateg);
            
            //$isJoined = false;
           // if ($checkExist['id_user_category'] != '' && $checkExist['status'] == '1') {
           //     $isJoined = true;
          //  }

            //send notif
            if ($masterCateg['subscribe_fcm'] != '') {
                $actionUser = $this->userModel->getTokenById($idUser);
                if ($titleNotif == ''){
                    $titleNotif = $isJoined ? "Category join by " . $actionUser['fullname'] : "Category unjoin by " . $actionUser['fullname'];
                }
                if ($desc == ''){
                    $desc = $masterCateg['description'];
                }
                $image = $masterCateg['image'];
                $dataFcm = array(
                    'title'   => $titleNotif,
                    'body'    => $desc . "\n#" . $masterCateg['title'],
                    "image"   => $image,
                    'payload' => array(
                        "keyname" => $isJoined ? 'join_category' : 'unjoin_category',
                        "categ" => $masterCateg,
                        "image"   => $image
                    ),
                );
                
                if ($groupPrivate == '1' ){
                    if ($checkExist['status'] == '3'){
                    $ownerUserCateg = $this->userModel->getTokenById($masterCateg['id_owner']);
                    $this->userModel->sendFCMMessage( $ownerUserCateg['token_fcm'], $dataFcm);
                } else {
                    $this->userModel->sendFCMMessage( $actionUser['token_fcm'], $dataFcm);
                    
                }
                } else{
                 //   $this->userModel->sendFCMMessage('/topics/' . $masterCateg['subscribe_fcm'], $dataFcm);
                }

            }
        }
        
        $arr = $dataCateg; 
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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
    
    //Request by User
    //Unjoin by User 
    public function request_unjoin_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        
        $dataCateg = array();
        
        if ($idUser != '' && $idPost != '') {
            $dataCateg = [$this->userPostModel->request_unjoin($this->postBody)];
            
          //  $masterCateg = $this->postModel->getById($idPost);
            $checkExist = $this->userPostModel->getByUserPost($idUser, $idPost);
            
            $isJoined = false;
            if ($checkExist['id_user_post'] != '' && $checkExist['status'] != '0') {
                $isJoined = true;
            }
            
                    //send notif
                    $singlePost = $this->postModel->getById($idPost);
                    if ($singlePost['id_user'] != '' && $idUser != '') {
                        $actionUser = $this->userModel->getTokenById($idUser);
                        $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
                                    
                        $desc = $singlePost['description'];
                        $image = $singlePost['image'];
                        if ($titleNotif == ''){
                          $titleNotif = $isJoined ? "Event unjoined by " . $actionUser['fullname'] : "Event request join by " . $actionUser['fullname'];                        
                        }
                        if ($descNotif == ''){
                            $descNotif = $desc . "\n#" . $categPost['title'];
                        }
                        $dataFcm = array(
                            'title'   => $titleNotif,
                            'body'    => $descNotif ,
                            "image"   => $image,
                            'payload' => array(
                                "keyname" => $isJoined ? 'unjoin_post' : 'request_post',
                                "post" => $singlePost,
                                "image"   => $image
                            ),
                        );
                        
                        $this->userModel->sendFCMMessage($ownerUser['token_fcm'], $dataFcm);
                        
                        
                    }
        }
        
        $arr = $dataCateg;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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

    //Idem request_unjoin_post
    public function request_show_gallery()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        //user to request
        $idUser = $this->postBody['iu'];
        //me
        $idMe = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        
        $dataCateg = array();
        
        if ($idUser != '' && $idMe != '') {
            $dataCateg = [$this->userGalleryModel->request_show_gallery($this->postBody)];
            
          //  $masterCateg = $this->postModel->getById($idMe);
            $checkExist = $this->userGalleryModel->getByUserPost($idUser, $idMe);
            
            $isGalleryVisible = false;
            if ($checkExist['id_user_gallery'] != '' && $checkExist['status'] == '1') {
                $isGalleryVisible = true;
            }
            
                    //send notif
                    $singlePost = $this->postModel->getById($idMe);
                    if ($singlePost['id_user'] != '' && $idUser != '') {
                        $targetUser = $this->userModel->getTokenById($idUser);
                        $meUser = $this->userModel->getTokenById($idMe);
                                    
                        
                        $image = $meUser['image'];
           
                     
                        $dataFcm = array(
                            'title'   => $titleNotif,
                            'body'    => $descNotif ,
                            "image"   => $image,
                            'payload' => array(
                                "keyname" =>  'show_gallery',
                                "post" => $targetUser,
                                "image"   => $image
                            ),
                        );
                        
                        $this->userModel->sendFCMMessage($targetUser['token_fcm'], $dataFcm);
                        
                        
                    }
        }
        
        $arr = $dataCateg;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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



    //Join (validate) by Admin
    public function join_unjoin_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        
        $dataCateg = array();
        
        if ($idUser != '' && $idPost != '') {
            $dataCateg = [$this->userPostModel->join_unjoin($this->postBody)];
            
           // $masterCateg = $this->postModel->getById($idPost);
            $checkExist = $this->userPostModel->getByUserPost($idUser, $idPost);
            
            $isJoined = false;
            if ($checkExist['id_user_post'] != '' && $checkExist['status'] != '0') {
                $isJoined = true;
            }

       //send notif
       $singlePost = $this->postModel->getById($idPost);
       if ($singlePost['id_user'] != '' && $idUser != '') {
           $actionUser = $this->userModel->getTokenById($idUser);
           $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
                       
           $desc = $singlePost['description'];
           $image = $singlePost['image'];
           if ($titleNotif == ''){
             $titleNotif = $isJoined ? "Participation accepted by " . $actionUser['fullname'] : "Event unjoined by " . $actionUser['fullname'];                        
           }
           if ($descNotif == ''){
            $descNotif = $desc;
           }
           $dataFcm = array(
               'title'   => $titleNotif,
               'body'    => $descNotif,
               "image"   => $image,
               'payload' => array(
                   "keyname" => $isJoined ? 'unjoin_post' : 'join_post',
                   "post" => $singlePost,
                   "image"   => $image
               ),
           );

           if ($isJoined){
            $this->userModel->sendFCMMessage($actionUser['token_fcm'], $dataFcm);
              }else{
            $this->userModel->sendFCMMessage($ownerUser['token_fcm'], $dataFcm);
        }
           
       }
        }
        
        $arr = $dataCateg;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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

    //Unjoin (reject) by Admin
    public function unjoin_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        
        $dataCateg = array();
        
        if ($idUser != '' && $idPost != '') {
            $dataCateg = [$this->userPostModel->unjoin($this->postBody)];
            
          //  $masterCateg = $this->postModel->getById($idPost);
            $checkExist = $this->userPostModel->getByUserPost($idUser, $idPost);
            
            $isJoined = true;

            
            //send notif
            $singlePost = $this->postModel->getById($idPost);
            if ($singlePost['id_user'] != '' && $idUser != '') {
                $actionUser = $this->userModel->getTokenById($idUser);
                $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
    
                
                $desc = $singlePost['description'];
                $image = $singlePost['image'];
                
                if ($titleNotif == ''){
                    $titleNotif =  "Event participation rejected by " . $actionUser['fullname'] ;
                }
                
                if ($descNotif == ''){
                    $descNotif = $desc;
                }
    
                $dataFcm = array(
                    'title'   => $titleNotif,
                    'body'    => $descNotif,
                    "image"   => $image,
                    'payload' => array(
                        "keyname" => 'unjoin_post' ,
                        "post" => $singlePost,
                        "image"   => $image
                    ),
                );
    
                $this->userModel->sendFCMMessage($actionUser['token_fcm'], $dataFcm);
                
            }
        }
        
        $arr = $dataCateg;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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

    public function join_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        $status = $this->postBody['status'];
        if ($status == ''){
            $status = 1;
        }
        
        $dataCateg = array();
        
        if ($idUser != '' && $idPost != '') {
            $dataCateg = [$this->userPostModel->join($this->postBody)];
            
          //  $masterCateg = $this->postModel->getById($idPost);
            $checkExist = $this->userPostModel->getByUserPost($idUser, $idPost);
            
            $isJoined = true;

            
            //send notif
            $singlePost = $this->postModel->getById($idPost);
            if ($singlePost['id_user'] != '' && $idUser != '') {
                $actionUser = $this->userModel->getTokenById($idUser);
                $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
    
                
                $desc = $singlePost['description'];
                $image = $singlePost['image'];
                
                if ($titleNotif == ''){
                    $titleNotif =  "Event participation validated by " . $actionUser['fullname'] ;
                }
                
                if ($descNotif == ''){
                    $descNotif = $desc;
                }
    
                $dataFcm = array(
                    'title'   => $titleNotif,
                    'body'    => $descNotif,
                    "image"   => $image,
                    'payload' => array(
                        "keyname" => 'join_post' ,
                        "post" => $singlePost,
                        "image"   => $image
                    ),
                );
                if ($singlePost['id_user'] !=  $idUser){
                    if ($status == '1'){
                     $this->userModel->sendFCMMessage($actionUser['token_fcm'], $dataFcm);
                    }elseif ($status == '4'){
                        $this->userModel->sendFCMMessage($ownerUser['token_fcm'], $dataFcm);
                    }
                }
                
            }
        }
        
        $arr = $dataCateg;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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

    //Cancell by Admin
    public function cancell_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $idUser = $this->postBody['iu'];
        $idPost = $this->postBody['ic'];
        $titleNotif = $this->postBody['titleNotif'];
        $descNotif = $this->postBody['descNotif'];
        
        $categPost = array();
        
        if ($idUser != '' && $idPost != '') {
            $singlePost = $this->postModel->cancell($this->postBody);

            $idCateg = $singlePost['id_category'];
            $categPost = $this->categModel->getById($idCateg);
            
          
            //send notif
            //$singlePost = $this->postModel->getById($idPost);
            if ($singlePost['id_user'] != '' && $idUser != '') {
            //    $actionUser = $this->userModel->getTokenById($idUser);
             //   $ownerUser = $this->userModel->getTokenById($singlePost['id_user']);
    
                
               // $desc = $singlePost['description'];
                $image = $singlePost['image'];
                
                
    
                $dataFcm = array(
                    'title'   => $titleNotif,
                    'body'    => $descNotif . "\n#" . $categPost['title'],
                    "image"   => $image,
                    'payload' => array(
                        "keyname" => 'cancell_post' ,
                        "post" => $singlePost,
                        "image"   => $image
                    ),
                );
    
           //     $this->userModel->sendFCMMessage('/topics/' . $categPost['subscribe_fcm'], $dataFcm);
                
            }
            
        }
        
        $arr = $categPost;
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Required data parameter",
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


    public function login()
    {
        $arr = array();
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $offset = 0;
        $limit = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $offset = (int) $exp[0];
            $limit = (int) $exp[1];
            
        }
        
        //123456    cfc5902918296762903710e9c9a65580
        if ($this->postBody['ps'] != '' &&  $this->postBody['em'] != '') {
        $passwrd = $this->generatePassword($this->postBody['ps']);
        $dataUser = $this->userModel->loginByEmail($this->postBody['em'], $passwrd);
        }

        if ($this->postBody['ph'] != '') {
        $dataUser = $this->userModel->loginByPhone2($this->postBody['ph']);
        }


        if ($this->postBody['is'] != '' && $dataUser['id_user'] != '') {
            $this->postBody['id'] = $dataUser['id_user'];
            $this->userModel->updateUser($this->postBody);
        }
        
        $arr = $dataUser; 
        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Email/Username & Password invalid",
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

    public function reset_password()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        
        $arr = array();
        $idUser = $this->postBody['iu'];
        $pass = $this->generatePassword($this->postBody['ps']);

        if ($idUser != '' && $pass != '') {
            $data = [
                "id_user" => $idUser,
                "password_user" =>  $pass
            ];

            $this->userModel->save($data);
            $arr = $this->userModel->getByUserAll($idUser);
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

    public function register()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        $arr = array();

        if ($this->postBody['ps'] != '' && $this->postBody['em'] != '') {
            
            $checkExist = $this->userModel->getByEmail($this->postBody['em']);
            

            if ($checkExist['id_user'] == '') {
                $this->postBody['ps'] = $this->generatePassword($this->postBody['ps']);
                
                $dataUser = $this->userModel->register($this->postBody);

                $arr = [$dataUser]; 
            }
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

    public function registerPhone()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        $arr = array();

        if ($this->postBody['ps'] != '' && $this->postBody['ph'] != '') {
            $checkExist = $this->userModel->getByPhone($this->postBody['ph']);
            
            if ($checkExist['id_user'] == '') {
                $this->postBody['ps'] = $this->generatePassword($this->postBody['ps']);
                $dataUser = $this->userModel->registerByPhone($this->postBody);
                
                $arr = [$dataUser]; 
            }
        }

        if (count($arr) < 1) {
            $json = array(
                "result" => $arr,
                "code" => "201",
                "message" => "Phone number already exist",
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

    public function checkEmailPhone() {
        $this->postBody = $this->authModel->authHeader($this->request);
        $arr = array();

        if ($this->postBody['em'] == '' && $this->postBody['ph'] == '') {
            
        }
        else {
            $checkExist = null;
            if ($this->postBody['ph'] != '') {
                $checkExist = $this->userModel->getByPhone($this->postBody['ph']);
            }
            else if ($this->postBody['em'] != '') {
                $checkExist = $this->userModel->getByEmail($this->postBody['em']);
            }

            if ($checkExist != null) {
                $arr = [$checkExist]; 
            }
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

    public function hash_password() {
        $this->postBody = $this->authModel->authHeader($this->request);
        print_r($this->generatePassword($this->postBody['ps']));
    }

    private function generatePassword($password) {
        return md5(sha1(hash("sha256", $password)));
    }

    public function checkPhone() {
        $this->postBody = $this->authModel->authHeader($this->request);
        $arr = array();

  
            $checkExist = null;
            if ($this->postBody['ph'] != '') {
                $checkExist = $this->userModel->getByPhone($this->postBody['ph']);
            }
      
            if ($checkExist != null) {
                $arr = [$checkExist]; 
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

    public function add_updatejson() {
        $this->postBody = $this->authModel->authHeader($this->request);
        $title =  $this->postBody['title'];
        $titleNotif =  $this->postBody['titleNotif'];
        $desc =  $this->postBody['description'];
        $descNotif =  $this->postBody['descNotif'];
        $image =  $this->postBody['image'];
        $status =  $this->postBody['status'];
        $id =  $this->postBody['id'];
        $idCatUp =  $this->postBody['idCategoryUp'];
        $group =  $this->postBody['group'];
        $private =  $this->postBody['private'];
        $latitude =  $this->postBody['lat'];
        $splitLat = explode(",", $latitude);
        $lat =  $splitLat[0];
        $lng =  $splitLat[1];
        $country =  $this->postBody['cc'];
        $location =  $this->postBody['loc'];
        $fun =  $this->postBody['fun'];
        $idOwner =  $this->postBody['idOwner'];

        $dataPost = array();
       
            if ($title != '' && $desc != '' && $image != '') {
                $dataModel = [
                    'id_category' => $id,
                    'id_category_up' => $idCatUp,
                    'title' => $title,                                    
                    'description' => $desc, 
                    'image' => $image,
                    'status' => ($status == 'on') ? 1 : 0,
                    'group' => ($group == '1') ? 1 : 0,
                    'private' => ($private == '1') ? 1 : 0,
                    'latitude' => $latitude, 
                    'lat'  => $lat,
                    'lng'  => $lng,
                    'country'  => $country,
                    'location' => $location, 
                    'fun' => ($fun == '1') ? 1 : 0,
                    'id_owner' => $idOwner,
                ];

                $dataPost = [$this->categModel->save($dataModel)];

                $dataModel = [
                    'id_category' => $id,
                    'id_category_up' => $idCatUp,
                    'title' => $titleNotif,                    
                    'body' => $descNotif,
                    'description' => $desc, 
                    'image' => $image,
                    'status' => ($status == 'on') ? 1 : 0,
                    'group' => ($group == '1') ? 1 : 0,
                    'private' => ($private == '1') ? 1 : 0,
                    'latitude' => $latitude, 
                    'lat'  => $lat,
                    'lng'  => $long,
                    'country'  => $country,
                    'location' => $location, 
                    'fun' => ($fun == '1') ? 1 : 0,
                    'id_owner' => $idOwner,
                ];

                try {
                    if ($id == '' ) {
                        //TODO : COMMENTED FOR DEV
                        //$this->userModel->sendFCMMessage($this->TOPIC_FCM, $dataModel);
                    }
                    //send notif fcm to topics
                } catch (Exception $e) {
                    // exception is raised and it'll be handled here
                    // $e->getMessage() contains the error message
                    //print("Error " . $e->getMessage());
                }


            }
        
            $dataPost = $this->users();
          //  $dataPost['users'] = $users; 

        if (count($dataPost)>0) {
            $json = array(
                "result" => $dataPost,
                "code" => "200",
                "message" => "Success",
            );
        }
        else {
            $json = array(
                "result" => "null" ,
                "code" => "208",
                "message" => "Data required parameter",
            );
        }
    
        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

}