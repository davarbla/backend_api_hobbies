<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\CategoryModel;

class Category extends BaseController
{

	private $authModel;
	private $sessLogin;

    private $categModel;

	public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
		$this->sessLogin = session();

        $this->categModel = new CategoryModel();
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