<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\UserModel;
use App\Models\PostModel;

use App\Models\CategoryModel;
use App\Models\UserCategoryModel;

class Upload extends BaseController
{
	protected $postBody;
    protected $authModel;
    protected $postModel;

    protected $userModel;
    protected $categModel;
    protected $userCategModel;

    private   $URL_BASE = 'https://hobbies.fboys.app/';
    private   $PATH = '/home/u439050121/domains/fboys.app/public_html/hobbies/upload/'; // echo getcwd() php script
    private   $TOPIC_FCM = '/topics/hobbiestopic';

    public function __construct()
    {
        $this->authModel = new AuthHeaderModel();
        $this->postModel = new PostModel();
        $this->userModel = new UserModel();

        $this->categModel = new CategoryModel();
        $this->userCategModel = new UserCategoryModel();
    }

    public function index()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        $limit = 0;
        $offset = 10;

        $getLimit = $this->request->getVar('lt');
        if ($getLimit != '') {
            $exp = explode(",", $getLimit);
            $limit = (int) $exp[1];
            $offset = (int) $exp[0];
        }

        //master user
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

    public function upload_post()
    {
        $this->postBody = $this->authModel->authHeader($this->request);
        //print_r($this->postBody);
        //die();
        $dataPost = array();
        //if ($this->postBody['img'] != '' && $this->postBody['iu'] != '' && $this->postBody['ic'] != '' && $this->postBody['ds'] != '') {
        if ( $this->postBody['iu'] != '' && $this->postBody['ic'] != '' ) {
            $dataPost = $this->postModel->saveUpdate($this->postBody);
        }

        if (count($dataPost)>0) {

            $idUser = $this->postBody['iu'];
            $dataUser = $this->userModel->getById($idUser);
            // update total_post user + 1
            $data = [
                'id_user'     => $idUser,
                'total_post'  => $dataUser['total_post'] + 1,
            ];
            $this->userModel->save($data);

            $idCateg = $this->postBody['ic'];
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

            //send notif fcm to topics
            //check file theme.dart  var fcmTopicName
            $desc = $this->postBody['ds'];

            $image = $this->postBody['img'];
            $dataFcm = array(
                'title'   => "New Event to Share " . $dataUser['fullname'],
                'body'    => $desc . "\n#" . $dataCateg['title'],
                "image"   => $image,
                'payload' => array(
                    "keyname" => 'new_post',
                    //"post" => $dataPost[0],
                    "image"   => $image
                ),
            );

            try {
                $this->userModel->sendFCMMessage($this->TOPIC_FCM, $dataFcm);
                //send notif fcm to topics
            } catch (Exception $e) {
                // exception is raised and it'll be handled here
                // $e->getMessage() contains the error message
                //print("Error " . $e->getMessage());
            }

            $json = array(
                "result" => $dataPost,
                "code" => "200",
                "message" => "Success",
            );
        }
        else {
            $json = array(
                "result" => $dataUser ,
                "code" => "208",
                "message" => "Data required parameter",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function upload_image_share()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        $filename = $this->postBody['filename'];
        $baseEncodeImage = $this->postBody['image'];

        $id = $this->postBody['id'];
        $dataUser = $this->userModel->getById($id);

        $binary = base64_decode($baseEncodeImage);
        $namefile = $filename;
        $ext = pathinfo($namefile, PATHINFO_EXTENSION);


        if ($namefile != '') {
            $target_dir = $this->PATH . "post/" ;

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $url_path = $this->URL_BASE . "upload/post/";


            $target_path = $target_dir;
            $now = date('YmdHis');
            $rand = rand(1111, 9999);
            $generatefile = $id . "_" . $now . "_" .$rand;
            $namefile = $generatefile . "." . $ext;

            $target_path = $target_path . $namefile;

            //chmod($target_path, 0777);
            //print($target_path);
            //die();
            //file_put_contents($target_path, $binary);

            $fh = fopen($target_path, 'w') or die("can't open file " . getcwd());
            chmod($target_path, 0777);
            fwrite($fh, $binary);
            fclose($fh);

            sleep(1);

            $foto = $url_path . $namefile;

            $json = array(
                "result" => array("file" => $foto),
                "code" => "200",
                "file" => $foto,
                "message" => "Upload share successful..."
            );
        }
        else {

            $json = array(
                "result" => array(),
                "code" => "209",
                "message" => "Upload failed",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function upload_image_user()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        $filename = $this->postBody['filename'];
        $baseEncodeImage = $this->postBody['image'];
        $imageNumber = $this->postBody['imageNumber'];
        $imageNumberdir = $this->postBody['imageNumber'];
        if ($imageNumberdir == '' || $imageNumberdir == '2' || $imageNumberdir == '3' || $imageNumberdir == '4') {
            $imageNumberdir = 'public';
        } else  if ($imageNumberdir == '5' || $imageNumberdir == '6' || $imageNumberdir == '7' ) {
            $imageNumberdir = 'event';
        } else{
            $imageNumberdir = 'private';
        }

        $id = $this->postBody['id'];
        $dataUser = $this->userModel->getById($id);

        $binary = base64_decode($baseEncodeImage);
        $namefile = $filename;
        $ext = pathinfo($namefile, PATHINFO_EXTENSION);

        if ($namefile != '') {
            $target_dir = $this->PATH . "user/" . $imageNumberdir ."/";

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $url_path = $this->URL_BASE . "upload/user/" . $imageNumberdir ."/";


            $target_path = $target_dir;
            $now = date('YmdHis');
            $rand = rand(1111, 9999);
            $generatefile = $id . "_photo_" . $now . "_" .$rand;
            $namefile = $generatefile . "." . $ext;
            $target_path = $target_path . $namefile;

            $fh = fopen($target_path, 'w') or die("can't open file " . getcwd());
            chmod($target_path, 0777);
            fwrite($fh, $binary);
            fclose($fh);

            sleep(1);

            //delete old data photo member file
            $filenm_deleted = basename($dataUser['image'. $imageNumber]);
            if ($filenm_deleted != 'avatar.png') {
                $file_path_to_delete = $this->PATH . "user/" . $imageNumberdir ."/" . $filenm_deleted;
               
                unlink($file_path_to_delete);
            }

            $foto = $url_path . $namefile;
            //update photo member
            $dataUpdate = [
                "id_user" => $id,
                "image". $imageNumber   => $foto,
                "date_updated" => date('YmdHis'),
            ];

            $this->userModel->save($dataUpdate);

            $json = array(
                "result" => array("file" => $foto),
                "code" => "200",
                "file" => $foto,
                "message" => "Upload share successful..."
            );
        }
        else {

            $json = array(
                "result" => array(),
                "code" => "209",
                "message" => "Upload failed",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

    public function delete_file()
    {
        $this->postBody = $this->authModel->authHeader($this->request);

        if ( $this->postBody['fl'] != '') {
            $file_path = $this->PATH . $this->postBody['fl'];
            unlink($file_path);
            $json = array(
                "result" => $file_path ,
                "code" => "200",
                "message" => "File $file_path Deleted",
            );
        }
        else {
            $json = array(
                "result" => $file_path ,
                "code" => "208",
                "message" => "Error File Error Unlink cannot be deleted due to an error",
            );
        }

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }

}