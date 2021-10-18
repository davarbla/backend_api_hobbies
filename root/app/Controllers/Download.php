<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\DownloadModel;

class Download extends BaseController
{

	private $authModel;
	private $sessLogin;

    private $downloadModel;

	public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
		$this->sessLogin = session();

        $this->downloadModel = new DownloadModel();
    }

	public function index()
	{
            
        $ac = $this->request->getVar('ac');

		$this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {

            $data = [
				"menu" => [ 
					"activeDownload" => "1" 
				],
			];
            
            $allData = $this->downloadModel->allByLimitPanel(1000, 0);
            $data['result'] = $allData;
            return view('alldownload_view', $data);
            
		}

		return view('login_view');
	}

}