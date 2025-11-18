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
    protected $sessLogin;
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

            if ($checkExist == null || !isset($checkExist['id_user']) || $checkExist['id_user'] == '') {
                // New user - hash the password
                $this->postBody['ps'] = $this->generatePassword($this->postBody['ps']);
                
            }
            else {
                // Existing user - use their existing data
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
        // Wrap EVERYTHING to prevent CodeIgniter's exception handler from causing issues
        try {
            // Disable CodeIgniter's automatic JSON response formatting to avoid FormatException
            @ini_set('display_errors', '0');
            error_reporting(0);
            
            // Get raw input and parse manually to avoid CodeIgniter JSON issues
            $rawInput = @file_get_contents('php://input');
            
            // Verify authentication first
            $token = @$this->request->headers();
        if (empty($token) || empty($token['X-Authentication'])) {
            $json = array(
                "result" => array(),
                "code" => "99",
                "message" => "Error: Access Denied, Authentication Key Token Invalid",
            );
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        
        $authkey = (string) $token['X-Authentication'];
        $authTokenBase64Encode = "ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo";
        $arr_token = explode(" ", $authkey);
        
        if (!isset($arr_token[1]) || $arr_token[1] != $authTokenBase64Encode) {
            $json = array(
                "result" => array(),
                "code" => "99",
                "message" => "Error: Access Denied, Authentication Key Token Invalid",
            );
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        
        // Parse JSON manually
        $this->postBody = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Parse Error: " . json_last_error_msg());
            $json = array(
                "result" => array(),
                "code" => "400",
                "message" => "Invalid JSON: " . json_last_error_msg(),
            );
            header('Content-Type: application/json');
            echo json_encode($json);
            die();
        }
        
        try {
            // Validate required fields
            if (empty($this->postBody['token'])) {
                $json = array(
                    "result" => array(),
                    "code" => "400",
                    "message" => "Missing FCM token",
                );
                header('Content-Type: application/json');
                echo json_encode($json);
                die();
            }
            
            if (empty($this->postBody['data'])) {
                $json = array(
                    "result" => array(),
                    "code" => "400",
                    "message" => "Missing notification data",
                );
                header('Content-Type: application/json');
                echo json_encode($json);
                die();
            }
            
            // Send FCM message
            $dataPush = $this->userModel->sendFCMMessage($this->postBody['token'], $this->postBody['data']);
            
            // Check if there was an error
            if (isset($dataPush['error'])) {
                error_log("FCM push_fcm error: " . json_encode($dataPush));
                $json = array(
                    "result" => $dataPush,
                    "code" => "500",
                    "message" => "FCM error: " . ($dataPush['message'] ?? 'Unknown error'),
                );
            } else if (empty($dataPush)) {
                $json = array(
                    "result" => array(),
                    "code" => "201",
                    "message" => "No response from FCM",
                );
            } else {
                $json = array(
                    "result" => $dataPush,
                    "code" => "200",
                    "message" => "Success",
                );
            }
        } catch (Exception $e) {
            error_log("FCM push_fcm exception: " . $e->getMessage());
            $json = array(
                "result" => array(),
                "code" => "500",
                "message" => "Server error: " . $e->getMessage(),
            );
        }

        } catch (Throwable $t) {
            // Catch ANY error including fatal errors to prevent CodeIgniter's JSON serialization bug
            error_log("FCM push_fcm throwable: " . $t->getMessage());
            $json = array(
                "result" => array(),
                "code" => "500",
                "message" => "Critical error: " . $t->getMessage(),
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