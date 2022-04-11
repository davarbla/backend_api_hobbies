<?php

namespace App\Models;

use CodeIgniter\Model;
//Idem UserPostModel
class UserGalleryModel extends Model
{
    protected $table      = 'tb_user_gallery';
    protected $primaryKey = 'id_user_gallery';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    //id_user: requested user
    //id_request: user who request (me)
    protected $allowedFields = ['id_user', 'id_request', 'count_interest', 'count_like', 'count_post', 'count_comment', 
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
                    ->where('id_request', "$iduser")                    
                    ->orderBy('date_created','asc')
                    ->findAll($limit, $offset);
    }

    public function saveChoice($array) {

        if ($array['id'] != '' && count($array['ids'])  > 0) {

            $this->where('id_user', $array['id'])->delete();

            foreach ($array['ids'] as $value) {
                $data = [
                    'id_user'   => $array['id'],
                    'id_request'  => $value,
                ];
                
                $this->save($data);
            }
        }

        return $this->getByUserId($array['id']);
    }
    //Idem request_unjoin
    public function request_show_gallery($array) {
        //user to request
        $idUser = $array['iu'];
        //me
        $idPost = $array['ic'];
        $status = $array['status'];
        
        if ($idUser != '' && $idPost != '') {
            $checkExist = $this->getByUserPost( $idPost, $idUser);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist['id_user_gallery'] != '') {

                    // no exist do join

                    $data = [
                        'id_user_gallery' => $checkExist['id_user_gallery'],
                        'id_user'   => $idPost,
             
                        'status' => $status,
                        //me
                        'id_request'  => $idUser,
                    ];


                
                $this->save($data);
                if ($sqlUpdate2 != '') {
                    $this->query($sqlUpdate2);
                }
            }
            else {

                $data = [
                    'id_user'   => $idPost,
            
                    'status' => $status,
                    //me
                    'id_request'  => $idUser
                ];

                
      
                $this->save($data);
                if ($sqlUpdate2 != '') {
                    $this->query($sqlUpdate2);
                }
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
                    ->where('id_request', $idPost)
                    ->first();
    }

    public function getById($id) {
        return $this->where('id_user_gallery', $id)
                    ->first();
    }
}