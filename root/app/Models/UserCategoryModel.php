<?php

namespace App\Models;

use CodeIgniter\Model;

class UserCategoryModel extends Model
{
    protected $table      = 'tb_user_category';
    protected $primaryKey = 'id_user_category';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_user', 'id_category', 'count_interest', 'count_like', 'count_post', 'count_comment', 
    'flag', 'status', 'date_created', 'date_updated'];
    
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    //protected $deletedField  = 'deleted_at';

    //protected $validationRules    = [];
    //protected $validationMessages = [];
    protected $skipValidation     = true;

    public function categUserByLimit($iduser, $limit=100, $offset=0) {
        // Join with category table to get full category details
        $query = $this->query(" SELECT uc.*, c.title, c.description, c.image, c.subscribe_fcm, 
                                c.total_interest, c.total_post, c.total_like, c.total_trivia, 
                                c.flag, c.status as category_status, c.date_created as category_date_created, 
                                c.date_updated as category_date_updated, c.id_category_up, c.private, 
                                c.group, c.latitude, c.location, c.id_owner, c.fun, c.lat, c.lng, c.country
                                FROM tb_user_category uc 
                                JOIN tb_category c ON uc.id_category = c.id_category 
                                WHERE uc.id_user = '$iduser' 
                                ORDER BY uc.count_interest DESC, uc.date_created ASC 
                                LIMIT $offset,$limit ");
        return $query->getResultArray();
    }

    public function saveChoice($array) {

        if ($array['id'] != '' && count($array['ids'])  > 0) {

            $this->where('id_user', $array['id'])->delete();

            foreach ($array['ids'] as $value) {
                $data = [
                    'id_user'   => $array['id'],
                    'id_category'  => $value,
                ];
                
                $this->save($data);
            }
        }

        return $this->getByUserId($array['id']);
    }

    public function join_unjoin($array) {
       
        $idUser = $array['iu'];
        $idCateg = $array['ic'];
        
        if ($idUser != '' && $idCateg != '') {
            $checkExist = $this->getByUserCateg($idUser, $idCateg);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist != null && $checkExist['id_user_category'] != '') {


                if ($checkExist['status'] == '1') {
                    // exist do unjoin
                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']-1,
                        'status' => 0,
                        'id_category'  => $idCateg
                    ];

                    //update post
                    $sqlUpdate2 = " UPDATE tb_category SET total_interest=total_interest-1 WHERE id_category='".$idCateg."' ";
                    $this->query($sqlUpdate2);
                }
                else {
                    // no exist do join

                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']+1,
                        'status' => 1,
                        'id_category'  => $idCateg,
                    ];

                    //update post
                    $sqlUpdate2 = " UPDATE tb_category SET total_interest=total_interest+1 WHERE id_category='".$idCateg."' ";
                    $this->query($sqlUpdate2);
                }
                
                $this->save($data);
               
            }
            else {

                $data = [
                    'id_user'   => $idUser,
                    'count_interest' => 1,
                    'status' => 1,
                    'id_category'  => $idCateg
                ];

                
                //update post
                $sqlUpdate2 = " UPDATE tb_category SET total_interest=total_interest+1 WHERE id_category='".$idCateg."' ";

                $this->save($data);
                $this->query($sqlUpdate2);
            }
        }

        return $this->getByUserCateg($idUser, $idCateg);
    }

    public function join_unjoin_private($array) {
       
        $idUser = $array['iu'];
        $idCateg = $array['ic'];
        $isJoinedstr = $array['isJoined'];
        $isJoined = false;
        if ($isJoinedstr == '1') {
               $isJoined = true;
        }
        
        if ($idUser != '' && $idCateg != '') {
            $checkExist = $this->getByUserCateg($idUser, $idCateg);
            
            $data = array();
            $sqlUpdate2 = "";

            if ($checkExist != null && $checkExist['id_user_category'] != '') {


                if ($checkExist['status'] == '1') {
                    // exist do unjoin
                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest']-1,
                        'status' => 0,
                        'id_category'  => $idCateg
                    ];

                    //update post - decrement total_interest when unjoining
                    $sqlUpdate2 = " UPDATE tb_category SET total_interest=total_interest-1 WHERE id_category='".$idCateg."' ";
                    $this->query($sqlUpdate2);
                } else if ($checkExist['status'] == '3' && $isJoined) {
                    // exist do unjoin (reject pending request)
                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest'],
                        'status' => 0,
                        'id_category'  => $idCateg
                    ];

                    //update post - no change to total_interest when rejecting pending
                } else if ($checkExist['status'] == '3' && !$isJoined) {
                    // exist do join (accept pending request)
                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest'],
                        'status' => 1,
                        'id_category'  => $idCateg
                    ];

                    //update post - increment total_interest when accepting pending
                    $sqlUpdate2 = " UPDATE tb_category SET total_interest=total_interest+1 WHERE id_category='".$idCateg."' ";
                    $this->query($sqlUpdate2);

                }else {
                    // no exist do join (new request for private group)
                    $data = [
                        'id_user_category' => $checkExist['id_user_category'],
                        'id_user'   => $idUser,
                        'count_interest' => $checkExist['count_interest'],
                        'status' => 3,
                        'id_category'  => $idCateg,
                    ];

                    //update post - no change to total_interest for pending requests
                }
                
                $this->save($data);
                
            }
            else {

                $data = [
                    'id_user'   => $idUser,
                    'count_interest' => 1,
                    'status' => 3,
                    'id_category'  => $idCateg
                ];


                $this->save($data);
                
            }
        }

        return $this->getByUserCateg($idUser, $idCateg);
    }


    public function getByUserId($id) {
        return $this->where('id_user', $id)
                    ->findAll();
    }

    public function getByUserCateg($idUser, $idCateg) {
        return $this->where('id_user', $idUser)
                    ->where('id_category', $idCateg)
                    ->first();
    }

    public function getById($id) {
        return $this->where('id_user_category', $id)
                    ->first();
    }
}