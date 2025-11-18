<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthHeaderModel extends Model
{

    private $sessLoginName = 'sess_login_hobbies';

    protected $table      = 'tb_userlogin';
    protected $primaryKey = 'id_userlogin';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['fullname', 'username', 'email', 'password', 'flag', 'status', 'date_created', 'date_updated'];
    
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    protected $skipValidation     = true;

    public function loginByEmail($email, $password) {
        return $this->where('status', '1')
                    ->where('email', $email)
                    ->where('password', $password)
                    ->first();
    }
    
    public function checkSession($session) {
        //$session = session();
        $arraySession = $session->get($this->sessLoginName);

        return isset($arraySession['username_ss']);
    }

    public function getDataSession($session) {
        return $session->get($this->sessLoginName);
    }

    public function addSession($session, $newdata) {
        $session->set($this->sessLoginName, $newdata);
    }

    public function removeSession($session) {
        //$session = session();
		$session->remove($this->sessLoginName);
    }
    
    public function authHeader($request) {
        $authTokenBase64Encode = "ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo";  //base64_encode(erhacorpdotcom:b1sm1ll4h);

        // Get the X-Authentication header value using getHeaderLine
        $authkey = $request->getHeaderLine('X-Authentication');
        
        if (empty($authkey)) {
            $authkey = $request->getHeaderLine('x-authentication');
        }
        
        if (empty($authkey)) {
            $this->exitError();
            exit(1);
        }

        // Split the header value (format: "Bearer <token>")
        $arr_token = explode(" ", $authkey);
        
        // Check if we have at least 2 parts and the token matches
        if (count($arr_token) < 2 || $arr_token[1] != $authTokenBase64Encode) { 
            $this->exitError();
            exit(1);
        }

        return $request->getJson(true);
    }

    public function exitError() {
        $json = array(
            "result" => array(),
            "code" => "99",
            "message" => "Error: Access Denied, Authentication Key Token Header Invalid",
        );

        //add the header here
        header('Content-Type: application/json');
        echo json_encode($json);
        die();
    }
}