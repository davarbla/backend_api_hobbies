<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use App\Models\FeedbackModel;
use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\DownloadModel;
use App\Models\InstallModel;
use App\Models\CategoryModel;

class Home extends BaseController
{

	private $authModel;
	private $sessLogin;

	private $feedbackModel;
	private $userModel;
	private $postModel;
	private $downloadModel;

	private $categModel;

	public function __construct()
    {
        $this->authModel = new AuthHeaderModel(); //Consider using CI's way of initialising models
		$this->sessLogin = session();

		$this->feedbackModel = new FeedbackModel();
		$this->userModel = new UserModel();
		$this->postModel = new PostModel();
		$this->downloadModel = new DownloadModel();
		$this->installModel = new InstallModel();
		$this->categModel = new CategoryModel();

    }

	public function index()
	{
		$this->sessLogin = session();	
		$check = $this->authModel->checkSession($this->sessLogin);
		if ($check) {
			$data = [
				"menu" => [ 
					"activeIndex" => "1" 
				],
			];

			$topFeedback = $this->feedbackModel->allByLimitPanel(10, 0);
            $data['result_feedback'] = $topFeedback;

			//install
			$totInstall = $this->installModel->getTotal();
			$data['total_install'] = $totInstall[0]['total'];

			//member
			$totMember = $this->userModel->getTotal();
			$data['total_member'] = $totMember[0]['total'];

			//post
			$totPost = $this->postModel->getTotal();
			$data['total_post'] = $totPost[0]['total'];

			//download
			$totDownload = $this->downloadModel->getTotal();
			$data['total_download'] = $totDownload[0]['total'];

			//category
			$totCategory = $this->categModel->getTotal();
			$data['total_category'] = $totCategory[0]['total'];

			//country
			$totCountry = $this->userModel->getTotal('', 'group');
			$data['total_country'] = count($totCountry);
			
			return view('home_view', $data);
		}

		//$test = htmlspecialchars(strip_tags("Cfkbffkl@"));
		//echo $test;
		//die();

		return view('login_view');
	}

	public function login()
	{
		$this->sessLogin = session();
		$em = $this->request->getVar('email');
		$ps = $this->request->getVar('password');

		if ($em != '' && $ps != ''){
			$passwd = $this->generatePassword($ps);
			$userLogin = $this->authModel->loginByEmail($em, $passwd);
			//print_r($userLogin);
			//die();
			
			//for admin@gmail.com password:  adminhobb2021     demo@gmail.com   password: userdemo2021
			if ($userLogin['id_userlogin'] != '') {
				$newdata = [
					'fullname_ss'  => $userLogin['fullname'],
					'username_ss'  => $userLogin['username'],
					'email_ss'     => $userLogin['email'],
					'user'		   => $userLogin,
					'logged_in'    => TRUE
				];
			}

			$this->authModel->addSession($this->sessLogin, $newdata);
		}
		
		$check = $this->authModel->getDataSession($this->sessLogin);
		
		return redirect()->to(base_url() . '/public'); 
	}

	public function logout()
	{
		$this->sessLogin = session();
		$this->authModel->removeSession($this->sessLogin);
		return redirect()->to(base_url() . '/public'); 
	}

	public function alluser()
	{
		return view('alluser_view');
	}

	public function hash_password() {
        $this->postBody = $this->authModel->authHeader($this->request);
        print_r($this->generatePassword($this->postBody['ps']));
    }

    private function generatePassword($password) {
        return md5(sha1(hash("sha256", $password)));
    }
}