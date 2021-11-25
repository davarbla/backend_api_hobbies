<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPostModel extends Model
{
    protected $table      = 'tb_user_post';
    protected $primaryKey = 'id_user_post';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_user', 'id_post', 'count_interest', 'count_like', 'count_post', 'count_comment', 
    'flag', 'status', 'date_created', 'date_updated'];
    
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    //protected $deletedField  = 'deleted_at';

    //protected $validationRules    = [];
    //protected $validationMessages = [];
    protected $skipValidation     = true;

    public function categUserByLimit($iduser, $limit=100, $offset=0) {
        return $this->where('status', '1')
                    ->where('id_user', "$iduser")
                    ->orderBy('count_interest','desc')
                    ->orderBy('date_created','asc')
                    ->findAll($limit, $offset);
    }

    public function saveChoice($array) {

        if ($array['id'] != '' && count($array['ids'])  > 0) {

            $this->where('id_user', $array['id'])->delete();

            foreach ($array['ids'] as $value) {
                $data = [
                    'id_user'   => $array['id'],
                    'id_post'  => $value,
                ];
                
                $this->save($data);
            }
        }

        return $this->getByUserId($array['id']);
    }

    public function request_unjoin($array) {
       
        $idUser = $array['iu'];
        $idPost = $array['ic'];
        
        if ($idUser != '' && $idPost != '') {
            $checkExist = $this->getByUserPost($idUser, $idPost);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist['id_user_post'] != '') {


                if ($checkExist['status'] == '1'||$checkExist['status'] == '3') {
                    // exist do unjoin
                    $data = [
                        'id_user_post' => $checkExist['id_user_post'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']-1,
                        'status' => 0,
                        'id_post'  => $idPost
                    ];
                    if ($checkExist['status'] == '1') {
                    //update post
                    $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user-1 WHERE id_post='".$idPost."' ";
                    }
                }
                else {
                    // no exist do join

                    $data = [
                        'id_user_post' => $checkExist['id_user_post'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']+1,
                        'status' => 3,
                        'id_post'  => $idPost,
                    ];

                    //update post (INUTIL! SUPRIMIR)
                    //$sqlUpdate2 = " UPDATE tb_post SET total_user=total_user WHERE id_post='".$idPost."' ";
                }
                
                $this->save($data);
                $this->query($sqlUpdate2);
            }
            else {

                $data = [
                    'id_user'   => $idUser,
                    'count_interest' => 1,
                    'status' => 3,
                    'id_post'  => $idPost
                ];

                
                //update post (INUTIL! SUPRIMIR)
                $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user WHERE id_post='".$idPost."' ";

                $this->save($data);
                if ($sqlUpdate2 != '') {
                $this->query($sqlUpdate2);
                }
            }
        }

        return $this->getByUserPost($idUser, $idPost);
    }

    public function join_unjoin($array) {
       
        $idUser = $array['iu'];
        $idPost = $array['ic'];
        
        if ($idUser != '' && $idPost != '') {
            $checkExist = $this->getByUserPost($idUser, $idPost);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist['id_user_post'] != '') {


                if ($checkExist['status'] == '1') {
                    // exist do unjoin
                    $data = [
                        'id_user_post' => $checkExist['id_user_post'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']-1,
                        'status' => 0,
                        'id_post'  => $idPost
                    ];

                    //update post
                    $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user-1 WHERE id_post='".$idPost."' ";
                }
                else {
                    // no exist do join

                    $data = [
                        'id_user_post' => $checkExist['id_user_post'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']+1,
                        'status' => 1,
                        'id_post'  => $idPost,
                    ];

                    //update post
                    $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user+1 WHERE id_post='".$idPost."' ";
                }
                
                $this->save($data);
                $this->query($sqlUpdate2);
            }
            else {

                $data = [
                    'id_user'   => $idUser,
                    'count_interest' => 1,
                    'status' => 1,
                    'id_post'  => $idPost
                ];

                
                //update post
                $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user+1 WHERE id_post='".$idPost."' ";

                $this->save($data);
                $this->query($sqlUpdate2);
            }
        }

        return $this->getByUserPost($idUser, $idPost);
    }

    public function unjoin($array) {
       
        $idUser = $array['iu'];
        $idPost = $array['ic'];
        
        if ($idUser != '' && $idPost != '') {
            $checkExist = $this->getByUserPost($idUser, $idPost);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist['id_user_post'] != '') {


                if ($checkExist['status'] != '0') {
                    // exist do unjoin
                    $data = [
                        'id_user_post' => $checkExist['id_user_post'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']-1,
                        'status' => 0,
                        'id_post'  => $idPost
                    ];

                    //update post
                    $sqlUpdate2 = " UPDATE tb_post SET total_user=total_user-1 WHERE id_post='".$idPost."' ";
                }
                
                
                $this->save($data);
                $this->query($sqlUpdate2);
            }
            
        }

        return $this->getByUserPost($idUser, $idPost);
    }

    public function getByUserId($id) {
        return $this->where('id_user', $id)
                    ->findAll();
    }

    public function getByUserPost($idUser, $idPost) {
        return $this->where('id_user', $idUser)
                    ->where('id_post', $idPost)
                    ->first();
    }

    public function getById($id) {
        return $this->where('id_user_post', $id)
                    ->first();
    }
}