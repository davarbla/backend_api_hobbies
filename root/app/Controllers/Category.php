<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\CategoryModel;
use App\Models\UserModel;

class Category extends BaseController
{
    private $TOPIC_FCM = '/topics/hobbiestopic';
	private $authModel;
	private $sessLogin;

    private $categModel;
    protected $userModel;

	public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
		$this->sessLogin = session();

        $this->categModel = new CategoryModel();
        $this->userModel = new UserModel();
    }

	public function index()
	{
            
        $ac = $this->request->getVar('ac');

		$this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {

            $data = [
				"menu" => [ 
					"activeCategory" => "1" 
				],
			];

            if ($ac == 'add') {
                $data = [
                    "menu" => [ 
                        "activeAddCategory" => "1" 
                    ],
                ];
                return view('editcategory_view', $data);
            }
            else if ($ac == 'edit') {
                $id = $this->request->getVar('id');
                $data['row'] = $this->categModel->getById($id);
                //print_r($data['row']);
                //die();
                
                return view('editcategory_view', $data);
            }
            else {
                $allDataCateg = $this->categModel->allByLimitPanel(1000, 0);
                $data['result'] = $allDataCateg;
			    return view('allcategory_view', $data);
            }
		}

		return view('login_view');
	}

    public function add_update() {
        $title = $this->request->getVar('title');
        $desc = $this->request->getVar('description');
        $image = $this->request->getVar('image');
        $status = $this->request->getVar('status');
        $id = $this->request->getVar('id');

        /*echo base_url();    
        echo "status " . $status. "<br/>";
        echo "Title : ". $title . " Image: " . $image . " Desc " . $desc;
        echo "ID " . $id. "<br/>";
        die();*/

        $this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {
            if ($title != '' && $desc != '' && $image != '') {
                $dataModel = [
                    'id_category' => $id,
                    'title' => $title,
                    'description' => $desc, 
                    'image' => $image,
                    'status' => ($status == 'on') ? 1 : 0,
                ];

                $this->categModel->save($dataModel);
            }
        }

        return redirect()->to(base_url() . '/public/category'); 
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
                    'location' => $location, 
                    'fun' => ($fun == '1') ? 1 : 0,
                    'id_owner' => $idOwner,
                ];

                $dataPost = $this->categModel->save($dataModel);

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
                    'location' => $location, 
                    'fun' => ($fun == '1') ? 1 : 0,
                    'id_owner' => $idOwner,
                ];

                try {
                    $this->userModel->sendFCMMessage($this->TOPIC_FCM, $dataModel);
                    //send notif fcm to topics
                } catch (Exception $e) {
                    // exception is raised and it'll be handled here
                    // $e->getMessage() contains the error message
                    //print("Error " . $e->getMessage());
                }


            }
        
          

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

    public function delete() {
        $this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {
            $dataSession = $this->authModel->getDataSession($this->sessLogin);

            if ($dataSession['user']['flag'] == '99') {
                $id = $this->request->getVar('id');
                $this->categModel->delete($id);
            }
        }

        return redirect()->to(base_url() . '/public/category'); 
    }

    
   
}