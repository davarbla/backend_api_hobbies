<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\FeedbackModel;

class Feedback extends BaseController
{

	private $authModel;
	private $sessLogin;

    private $feedbackModel;

	public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
		$this->sessLogin = session();

        $this->feedbackModel = new FeedbackModel();
    }

	public function index()
	{
            
        $ac = $this->request->getVar('ac');

		$this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {

            $data = [
				"menu" => [ 
					"activeFeedback" => "1" 
				],
			];
            
            $allData = $this->feedbackModel->allByLimitPanel(1000, 0);
            $data['result'] = $allData;
            return view('allfeedback_view', $data);
            
		}

		return view('login_view');
	}

}