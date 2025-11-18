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
    'date_created', 'date_updated','date_img_upd','height','weight','age','position','protection','relationship','bodyColor','bodyShape','hair','publish','vip','superAdmin','public','friends','fun','face','lat','lng','message', 'reliable','sexy','ugly' ];

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
            ORDER BY a.date_img_upd DESC, a.total_comment DESC, a.fullname ASC
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
    
    public function allByLimitCountry($limit=100, $offset=0, $country=ZZ) {
        return $this->where('status','1')
                    ->where('country',"$country")                    
                    ->orderBy('total_post','desc')
                    ->orderBy('total_comment','desc')
                    ->orderBy('fullname','asc')
                    ->findAll($limit, $offset);
    }

    public static function boundingBox($latitude, $longitude, $distance)
    {
        $latLimits = [deg2rad(-90), deg2rad(90)];
        $lonLimits = [deg2rad(-180), deg2rad(180)];
     
        $radLat = deg2rad($latitude);
        $radLon = deg2rad($longitude);
     
        if ($radLat < $latLimits[0] || $radLat > $latLimits[1]
            || $radLon < $lonLimits[0] || $radLon > $lonLimits[1]) {
            throw new \Exception("Invalid Argument");
        }
     
        // Angular distance in radians on a great circle,
        // using Earth's radius in miles.
        $angular = $distance / 3958.762079;
     
        $minLat = $radLat - $angular;
        $maxLat = $radLat + $angular;
     
        if ($minLat > $latLimits[0] && $maxLat < $latLimits[1]) {
            $deltaLon = asin(sin($angular) / cos($radLat));
            $minLon = $radLon - $deltaLon;
     
            if ($minLon < $lonLimits[0]) {
                $minLon += 2 * pi();
            }
     
            $maxLon = $radLon + $deltaLon;
     
            if ($maxLon > $lonLimits[1]) {
                $maxLon -= 2 * pi();
            }
        } else {
            // A pole is contained within the distance.
            $minLat = max($minLat, $latLimits[0]);
            $maxLat = min($maxLat, $latLimits[1]);
            $minLon = $lonLimits[0];
            $maxLon = $lonLimits[1];
        }
     
        print($box['minLat']);
        print_r($dataUserCateg);
        return [
            'minLat' => rad2deg($minLat),
            'minLon' => rad2deg($minLon),
            'maxLat' => rad2deg($maxLat),
            'maxLon' => rad2deg($maxLon),
        ];
    }

    public function allByLimitCountryDistance($longitude, $latitude, $limit=10000, $offset=0, $country='ZZ', $miles=100, $status=1) {
        $getlimit = "$offset,$limit";
        $box = static::boundingBox(floatval($latitude), floatval($longitude), $miles);

        $query   = $this->query(" SELECT a.* FROM tb_user a 
        WHERE a.status='".$status."' 
        AND a.country='".$country."' 
        AND lat BETWEEN ".$box['minLat']." AND ".$box['maxLat']." 
        AND lng BETWEEN ".$box['minLon']."  AND ".$box['maxLon']." 
        order by (abs(lng-".$longitude.")/2) + (abs(lat-".$latitude.")/2) 
        LIMIT ".$getlimit." ");
        
        $results = $query->getResultArray();

        return $results;
    }

    public function getLastId() {
        return $this->orderBy('id_user','desc')
                    ->first();
    }

    public function updateUser($array) {
        if ($array['id']!='') {
            $splitLat = explode(",", $array['lat']);
            $data = [
                'id_user'       => $array['id'],
                'uid_fcm'       => $array['uf'],
                'id_install'    => $array['is'],
                'latitude'  => $array['lat'],
                'lat'  => $splitLat[0],
                'lng'  => $splitLat[1],
                'location'  => $array['loc'],
                'country'       => $array['cc'],
            ];

            $this->save($data);
        }

        return $this->getById($array['id']);
    }

    //idem updateUser
    public function deleteUserMessage($array) {
        if ($array['id']!='') {
            $splitLat = explode(",", $array['lat']);
            $data = [
                'id_user'       => $array['id'],
                'uid_fcm'       => $array['uf'],
                'id_install'    => $array['is'],
                'latitude'  => $array['lat'],
                'lat'  => $splitLat[0],
                'lng'  => $splitLat[1],
                'location'  => $array['loc'],
                'country'       => $array['cc'],
                'message'       => $array['msg'],
            ];

            $this->save($data);
        }

        return $this->getById($array['id']);
    }

    public function register($array) {
        try {
            // Log the received data for debugging
            log_message('debug', 'UserModel::register - Received data: ' . print_r($array, true));

            // Validate required fields
            if (!isset($array['em']) || !isset($array['fn']) || !isset($array['is']) || 
                empty($array['em']) || empty($array['fn']) || empty($array['is'])) {
                log_message('error', 'UserModel::register - Missing required fields. Array: ' . print_r($array, true));
                throw new \Exception('Missing required fields (email, fullname, or install ID)');
            }

            // Check if user already exists
            $check = $this->getByEmail($array['em']);
            if (!empty($check) && !empty($check['id_user'])) {
                log_message('debug', 'User already exists with email: ' . $array['em']);
                return $check; // Return existing user
            }

            // Generate a username if not provided
            $username = $array['us'] ?? '';
            if (empty($username)) {
                $splitname = explode(" ", strtolower($array['fn']));
                $lastRow = $this->getLastId();

                $plusOne = 1; // Default value
                if (!empty($lastRow['id_user'])) {
                    $plusOne = (int) $lastRow['id_user'] + 1;
                }

                $username = $this->generate_unique_username(
                    $splitname[0],
                    $splitname[1] ?? '',
                    (string)$plusOne
                );
                log_message('debug', 'Generated username: ' . $username);
            }

            // Prepare user data according to the actual database schema
            $data = [
                'id_install'    => $array['is'],
                'email'         => $array['em'],
                'phone'         => $array['ph'] ?? '',
                'fullname'      => $array['fn'],
                'username'      => $username,
                'uid_fcm'       => $array['uf'] ?? '',
                'password_user' => $array['ps'] ?? '',
                'latitude'      => $array['lat'] ?? '0,0',
                'location'      => $array['loc'] ?? '',
                'country'       => $array['cc'] ?? 'US',
                'status'        => 1, // Active user
                'flag'          => 1, // Default flag
                'timestamp'     => date('Y-m-d H:i:s'),
                'date_created'  => date('Y-m-d H:i:s'),
                'date_updated'  => date('Y-m-d H:i:s'),
                // Set default values for required fields
                'total_post'    => 0,
                'total_like'    => 0,
                'total_comment' => 0,
                'total_download' => 0,
                'total_follower' => 0,
                'total_following' => 0,
                'subscribe_fcm' => 1
            ];

            log_message('debug', 'Saving user with data: ' . print_r($data, true));
            
            // Save the user and get the insert ID
            $result = $this->save($data);
            
            if ($result === false) {
                $errors = $this->errors();
                log_message('error', 'Failed to save user. Errors: ' . print_r($errors, true));
                throw new \Exception('Failed to save user data: ' . implode(', ', $errors));
            }

            // Get the newly created user
            $userId = $this->getInsertID();
            if (empty($userId)) {
                log_message('error', 'Failed to get insert ID after saving user');
                throw new \Exception('Failed to retrieve user ID after registration');
            }
            
            // Get the complete user data
            $newUser = $this->find($userId);
            if (empty($newUser)) {
                log_message('error', 'Failed to retrieve newly created user with ID: ' . $userId);
                throw new \Exception('Failed to retrieve user data after registration');
            }

            $newUser = $this->find($userId);
            if (empty($newUser)) {
                log_message('error', 'Failed to retrieve user after registration');
                throw new \Exception('Failed to retrieve user after registration');
            }

            log_message('debug', 'User registered successfully: ' . print_r($newUser, true));
            return $newUser;

        } catch (\Exception $e) {
            log_message('error', 'Exception in UserModel::register: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to be caught by the controller
        }
    }

    public function registerByPhone($array) {

        if (empty($array['ph']) || empty($array['fn'])) {
            return null;
        }

        $username = isset($array['us']) ? $array['us'] : '';
        $userId = isset($array['id']) ? $array['id'] : '';
        $userImage = isset($array['img']) ? $array['img'] : '';
        
        if ($userId == '' && $username == '') {
            $splitname = explode(" ", strtolower($array['fn']));
            $lastRow = $this->getLastId();

            $plusOne = 0;
            if (isset($lastRow['id_user']) && $lastRow['id_user'] != '') {
                $plusOne = (int) $lastRow['id_user'];
            }

            $plusOne = $plusOne + 1;
            // Handle case where fullname might not have a space
            $firstname = $splitname[0];
            $lastname = isset($splitname[1]) ? $splitname[1] : $splitname[0];
            $username = $this->generate_unique_username($firstname, $lastname, "$plusOne");
        }
        
        $splitLat = explode(",", $array['lat']);
        
        // Ensure id_install is not null
        $idInstall = isset($array['is']) && $array['is'] != null && $array['is'] != 'null' 
            ? $array['is'] 
            : 'phone_' . time() . '_' . rand(1000, 9999);
        
        $data = [
            'id_user'   => $userId,
            'id_install'   => $idInstall,
            'email'    => $array['em'],
            'phone'         => $array['ph'],
            'fullname'    => $array['fn'],
            'image'         => $userImage != '' ? $userImage : 'https://hobbies.fboys.app/upload/assets/avatar.jpg',
            'username'         => $username,
            'uid_fcm'         => $array['uf'],
            'password_user'  => $array['ps'],
            'latitude'  => $array['lat'],
            'lat'  =>  isset($splitLat[0]) ? $splitLat[0] : '0',
            'lng'  =>  isset($splitLat[1]) ? $splitLat[1] : '0',
            'location'  => $array['loc'],
            'country'       => $array['cc'],
        ];

        //print_r($data);
        //die();

        $check = $this->getByPhone($array['ph']);
        if ($check != null && isset($check['id_user']) && $check['id_user'] != '' && $check['id_user'] != '0') {
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
        $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b
            LEFT JOIN tb_install c ON b.id_install=c.id_install
            WHERE b.id_user='".$id."' ");
        $result1 = $query1->getResultArray();
        
        // Return null if user not found, otherwise return first result
        if (empty($result1)) {
            return null;
        }
        return $result1[0];
    }

    public function isAvailable($userName){
       $check = $this->where('username', $userName)->first();

        if ($check === null || $check['id_user'] != '' || strlen(trim($userName)) < 8) {
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

        // Validate token before attempting to send
        if (empty($token) || $token === null || strlen($token) < 50) {
            error_log("FCM Error: Invalid or empty token provided - Token: " . substr($token, 0, 20));
            return array('error' => 'invalid_token', 'message' => 'FCM token is invalid or empty');
        }

        // Validate required data fields
        if (!isset($data_array['title']) || !isset($data_array['body'])) {
            error_log("FCM Error: Missing required fields (title or body)");
            return array('error' => 'missing_fields', 'message' => 'Title or body is missing');
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $data = array(
            'notification' => array(
                "title" => $data_array['title'],
                "body"  => $data_array['body'],
                'image'  => $data_array['image'] ?? '',
                'imageUrl' => $data_array['image'] ?? '',
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                'priority' =>  'high',
                'sound' => 'default'
            ),
            'data' => $data_array['payload'] ?? array(),
            // Set Android priority to "high"
            'android' => array(
                'priority'=> "high",
                'image'  => $data_array['image'] ?? '',
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
            error_log("FCM: Sending notification to token: " . substr($token, 0, 30) . "...");
            error_log("FCM: Notification title: " . $data_array['title']);
            error_log("FCM: Request data: " . json_encode($data));
            
            $result =  @file_get_contents($url, false, $context);
            
            // Log HTTP response headers
            if (isset($http_response_header)) {
                error_log("FCM: HTTP Response Headers: " . json_encode($http_response_header));
            }
            
            // Check for HTTP errors
            if ($result === false) {
                $error = error_get_last();
                error_log("FCM Error: Failed to connect to FCM - " . ($error['message'] ?? 'Unknown error'));
                error_log("FCM Error: Check if server can reach https://fcm.googleapis.com");
                return array('error' => 'connection_failed', 'message' => 'Could not connect to FCM server');
            }
            
            // Ensure result is not empty
            if (empty($result)) {
                error_log("FCM Error: Empty response from FCM server");
                error_log("FCM Error: This usually means the server key is invalid or request was blocked");
                return array('error' => 'empty_response', 'message' => 'Empty response from FCM');
            }
            
            $decoded = json_decode($result, true);
            
            // Ensure decoded is an array
            if (!is_array($decoded)) {
                error_log("FCM Error: Invalid JSON response from FCM");
                return array('error' => 'invalid_json', 'message' => 'Invalid response from FCM');
            }
            
            // Log the FCM response for debugging
            error_log("FCM Response: " . json_encode($decoded));
            
            // Check for FCM errors
            if (isset($decoded['failure']) && $decoded['failure'] > 0) {
                error_log("FCM Error: Failed to send notification - " . json_encode($decoded['results']));
            } else if (isset($decoded['success']) && $decoded['success'] > 0) {
                error_log("FCM Success: Notification sent successfully");
            }
            
            // Return only the decoded array (no resources or objects)
            return $decoded;
            //send notif fcm to topics
        } catch (Exception $e) {
            // exception is raised and it'll be handled here
            error_log("FCM Exception: " . $e->getMessage());
            error_log("FCM Exception Trace: " . $e->getTraceAsString());
            return array('error' => 'exception', 'message' => $e->getMessage());
        } catch (Throwable $t) {
            // Catch any other errors including parse errors
            error_log("FCM Throwable: " . $t->getMessage());
            return array('error' => 'throwable', 'message' => $t->getMessage());
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