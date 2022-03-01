<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'tb_user';
    protected $primaryKey = 'id_user';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['fullname', 'username', 'phone', 'email', 'gender', 'about',
    'image','image2','image3','image4','image5','image6','image7','image8','image9','image10', 'location', 'latitude', 'country', 'id_install', 'uid_fcm', 'total_post',
    'total_like', 'total_download', 'total_comment', 'total_follower', 'total_following',
    'password_user', 'timestamp', 'flag', 'status',
    'date_created', 'date_updated', 'height','weight','age','position','protection','relationship','bodyColor','bodyShape','hair','publish','vip','superAdmin','public','friends','fun','face'];

    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';

    protected $skipValidation     = true;

    private $keyServerFCM = 'AAAAjiXWTng:APA91bFp5VB1eRMotmjj_DX9HH6kfjx96r7IDfYlRDBOi19t0ywGWIpUI8r7c8B93BuwldjgQNcxvkaDL7LDxT4h5reSPPSn80GZYFHoe5TG5-Nc_nTjkn4s8c2bWdHydr_Lb5jVn5Vb';

    public function getTotal($os='', $group='') {
        $sql = " SELECT count(id_user) as total FROM tb_user ";
        if ($os != '') {
            $sql = " SELECT count(a.id_user) as total FROM tb_user a, tb_install b
                WHERE a.id_install=b.id_install
                AND b.os_platform='".$os."' ";
        }
        else if ($group != '') {
            $sql = " SELECT count(id_user) as total FROM tb_user
                GROUP BY country ";
        }

        $query   = $this->query($sql);
        $results = $query->getResultArray();
        return $results;
    }

    public function allByLimitPanel($limit=100, $offset=0) {
        $getlimit = "$offset,$limit";

        $query   = $this->query(" SELECT a.*, b.os_platform FROM tb_user a, tb_install b
            WHERE a.id_install=b.id_install
            ORDER BY a.date_updated DESC, a.total_comment DESC, a.fullname ASC
            LIMIT ".$getlimit." ");

        return $query->getResultArray();
    }

    // 123456    *6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9
    public function loginByUsername($username, $password) {
        return $this->where('status', '1')
                    ->where('username', $username)
                    ->where('password_user', $password)
                    ->findAll();
    }

    public function loginByEmail($email, $password) {
        return $this->where('status', '1')
                    ->where('email', $email)
                    ->where('password_user', $password)
                    ->findAll();
    }

    public function loginByPhone($phone, $password) {
        return $this->where('status', '1')
                    ->where('phone', $phone)
                    ->where('password_user', $password)
                    ->findAll();
    }

    public function loginByPhone2($phone ) {
        return $this->where('status', '1')
                    ->where('phone', $phone)                    
                    ->findAll();
    }


    public function getByUserAll($id) {

        $query   = $this->query(" SELECT a.*, b.os_platform, b.token_fcm, b.token_forgot
            FROM tb_user a, tb_install b
            WHERE a.id_install=b.id_install
            AND a.id_user='".$id."' ");

        return $query->getResultArray();
    }

    public function allByLimit($limit=100, $offset=0) {
        return $this->where('status','1')
                    ->orderBy('total_post','desc')
                    ->orderBy('total_comment','desc')
                    ->orderBy('fullname','asc')
                    ->findAll($limit, $offset);
    }

    public function getLastId() {
        return $this->orderBy('id_user','desc')
                    ->first();
    }

    public function updateUser($array) {
        if ($array['id']!='') {
            $data = [
                'id_user'       => $array['id'],
                'uid_fcm'       => $array['uf'],
                'id_install'    => $array['is'],
                'latitude'  => $array['lat'],
                'location'  => $array['loc'],
                'country'       => $array['cc'],
            ];

            $this->save($data);
        }

        return $this->getById($array['id']);
    }

    public function register($array) {

        if ($array['em'] == '' || $array['fn'] == '' || $array['is'] == '') {
            return null;
        }

        $username = $array['us'];
        if ($array['id'] == '' && $username == '') {
            $splitname = explode(" ", strtolower($array['fn']));
            $lastRow = $this->getLastId();


            $plusOne = 0;
            if ($lastRow['id_user'] != '') {
                $plusOne = (int) $lastRow['id_user'];
            }

            $plusOne = $plusOne + 1;
            $username = $this->generate_unique_username($splitname[0], $splitname[1],  "$plusOne");
            //print_r($username);
            //die();
        }

        //$datenow = date('YmdHis');
        $data = [
            'id_user'       => $array['id'],
            'id_install'    => $array['is'],
            'email'         => $array['em'],
            'phone'         => $array['ph'],
            'fullname'      => $array['fn'],
           // 'image'         => 'https://hobbies.in-news.id/upload/avatar.png',
            'username'         => $username,
            'uid_fcm'         => $array['uf'],
            'password_user'  => $array['ps'],
            'latitude'  => $array['lat'],
            'location'  => $array['loc'],
            'country'       => $array['cc'],
        ];

        //print_r($data);
        //die();

        $check = $this->getByEmail($array['em']);
        if ($check['id_user'] != '' && $check['id_user'] != '0') {
            $data['id_user'] = $check['id_user'];
        }

        //print_r($data);
        //die();

        $this->save($data);

        return $this->getByEmail($array['em']);
    }

    public function registerByPhone($array) {

        if ($array['ph'] == '' || $array['fn'] == '') {
            return null;
        }

        $username = $array['us'];
        if ($array['id'] == '' && $username == '') {
            $splitname = explode(" ", strtolower($array['fn']));
            $lastRow = $this->getLastId();

            $plusOne = 0;
            if ($lastRow['id_user'] != '') {
                $plusOne = (int) $lastRow['id_user'];
            }

            $plusOne = $plusOne + 1;
            $username = $this->generate_unique_username($splitname[0], $splitname[1], "$plusOne");
        }

        $data = [
            'id_user'   => $array['id'],
            'id_install'   => $array['is'],
            'email'    => $array['em'],
            'phone'         => $array['ph'],
            'fullname'    => $array['fn'],
            'image'         => $array['img'] != '' ? $array['img'] : 'https://hobbies.fboys.app/upload/assets/avatar.jpg',
            'username'         => $username,
            'uid_fcm'         => $array['uf'],
            'password_user'  => $array['ps'],
            'latitude'  => $array['lat'],
            'location'  => $array['loc'],
            'country'       => $array['cc'],
        ];

        //print_r($data);
        //die();

        $check = $this->getByPhone($array['ph']);
        if ($check['id_user'] != '' && $check['id_user'] != '0') {
            $data['id_user'] = $check['id_user'];
        }

        $this->save($data);

        return $this->getByPhone($array['ph']);
    }

    public function getByEmail($email) {
        return $this->where('email', $email)
                    ->first();
    }

    public function getByPhone($phone) {
        return $this->where('phone', $phone)
                    ->first();
    }

    public function getById($id) {
        return $this->where('id_user', $id)
                    ->first();
    }

    public function getTokenById($id) {
        $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c
            WHERE b.id_install=c.id_install
            AND b.id_user='".$id."' ");
        $result1 = $query1->getResultArray();
        return $result1[0];
    }

    public function isAvailable($userName){
       $check = $this->where('username', $userName)->first();

        if ( $check['id_user'] != '' || strlen(trim($userName)) < 8 ) {
             //echo 'User with this username already exists!';
             return false;
        } else {
            return true;
        }
    }

    public function generate_unique_username($firstname, $lastname, $userId){
        $userNamesList = array();
        $firstChar = str_split($firstname, 1)[0];
        $firstTwoChar = str_split($firstname, 2)[0];
        /**
         * an array of numbers that may be used as suffix for the user names index 0 would be the year
         * and index 1, 2 and 3 would be month, day and hour respectively.
         */
        $numSufix = explode('-', date('Y-m-d-H'));

        // create an array of nice possible user names from the first name and last name
        array_push($userNamesList,
            $firstname,                 //james
            $lastname,                 // oduro
            $firstname.$lastname,       //jamesoduro
            $firstname.'.'.$lastname,   //james.oduro
            $firstname.'-'.$lastname,   //james-oduro
            $firstChar.$lastname,       //joduro
            $firstTwoChar.$lastname,    //jaoduro,
            $firstname.$numSufix[0],    //james2019
            $firstname.$numSufix[1],    //james12 i.e the month of reg
            $firstname.$numSufix[2],    //james28 i.e the day of reg
            $firstname.$numSufix[3]     //james13 i.e the hour of day of reg
        );


        $isAvailable = false; //initialize available with false
        $index = 0;
        $maxIndex = count($userNamesList) - 1;

        // loop through all the userNameList and find the one that is available
        do {
            $availableUserName = $userNamesList[$index];
            $isAvailable = $this->isAvailable($availableUserName);
            $limit =  $index >= $maxIndex;
            $index += 1;
            if($limit){
                break;
            }

        } while (!$isAvailable );

        // if all of them is not available concatenate the first name with the user unique id from the database
        // Since no two rows can have the same id. this will sure give a unique username
        if(!$isAvailable){
            return $firstname.$userId;
        }
        return $availableUserName;
    }

    //send FCM notif
    public function sendFCMMessage($token, $data_array){
        //$keyServerFCM = 'AAAAInjYsHU:APA91bEirGDQHM1Vdp64CH45KCIEzPXh871At1mOibQpE4hB3uXXWwq7iWPDg-fC9RcKSq0d52LnYH9reILWokvDsqzjL6dFEuzm7MTOgFJ-movuUgcp1p3pQbzTUaKnx9hf3X_xEOg-';

        $url = 'https://fcm.googleapis.com/fcm/send';
        $data = array(
            'notification' => array(
                "title" => $data_array['title'],
                "body"  => $data_array['body'],
                'image'  => $data_array['image'],
                'imageUrl' => $data_array['image'],
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                'priority' =>  'high',
                'sound' => 'default'
            ),
            'data' => $data_array['payload'],
            // Set Android priority to "high"
            'android' => array(
                'priority'=> "high",
                'image'  => $data_array['image'],
            ),
            // Add APNS (Apple) config
            'apns' => array(
                'payload' => array(
                    'aps' => array(
                        'contentAvailable' => true,
                    ),
                ),
                'headers' => array(
                    "apns-push-type" => "background",
                    "apns-priority" => "5", // Must be `5` when `contentAvailable` is set to true.
                    "apns-topic" => "io.flutter.plugins.firebase.messaging", // bundle identifier
                ),
            ),
            'priority' => 'high',
            "to" => $token
        );

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode($data),
                'header'=>  "Content-Type: application/json\r\n" .
                            "Accept: application/json\r\n" .
                            "Authorization: key=" . $this->keyServerFCM
            )
        );

        $context  = stream_context_create( $options );

        try {
            $result =  file_get_contents($url, false, $context);
            return json_decode($result, true);
            //send notif fcm to topics
        } catch (Exception $e) {
            // exception is raised and it'll be handled here
            // $e->getMessage() contains the error message
            //print("Error " . $e->getMessage());
        }

        return array();
    }

}

/* id_user, fullname, username, phone, email, about,
image, location, latitude, id_install, uid_fcm, total_post,
total_like, total_comment, total_follower, total_following,
password_user, timestamp, flag, status,
date_created, date_updated, height, weight,age, position,protection,relationship,bodyColor,bodyShape,hair
*/