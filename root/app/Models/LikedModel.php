<?php

namespace App\Models;

use CodeIgniter\Model;

class LikedModel extends Model
{
    protected $table      = 'tb_liked';
    protected $primaryKey = 'id_liked';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_category', 'id_post', 'id_user', 'is_liked', 'flag', 'status', 'date_created', 'date_updated'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    //protected $deletedField  = 'deleted_at';
    //protected $validationRules    = [];
    //protected $validationMessages = [];
    protected $skipValidation     = true;

    public function allByLimit($limit=100, $offset=0, $status=1) {
        return $this->where('status', $status)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function allByLimitByIdUser($idUser, $limit=100, $offset=0, $status=1) {
        return $this->where('status', $status)
                    ->where('id_user', $idUser)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function allByLimitByIdCateg($idCateg, $limit=100, $offset=0, $status=1) {
        return $this->where('status', $status)
                    ->where('id_category', $idCateg)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function allByLimitByIdPost($idPost, $limit=100, $offset=0, $status=1) {
        return $this->where('status', $status)
                    ->where('id_post', $idPost)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function getAllByIdUser($idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        $sql = " SELECT a.* FROM tb_post a, tb_liked b 
            WHERE a.id_post=b.id_post
            AND b.flag=1 AND b.status=1 
            AND b.is_liked=1 
            AND a.status='".$status."' 
            AND b.id_user='".$idUser."'
            ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
            LIMIT ".$getlimit." ";

        //print_r($sql);
        //die();

        $query   = $this->query($sql);


        $results = $query->getResultArray();
        //print_r($results);
        //die();
        
        $return_array = array();
        $i = 0;
        foreach ($results as $row) {
            $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c WHERE b.id_install=c.id_install 
                AND b.id_user='".$row['id_user']."' ");
            $result1 = $query1->getResultArray();
            $row['user'] =  $result1[0];

            $query0   = $this->query(" SELECT a.is_liked FROM tb_liked a WHERE a.id_post='".$row['id_post']."'  
                AND a.id_user='".$idUser."' ");
            $result0 = $query0->getResultArray();
            $row['is_liked'] =  $result0[0]['is_liked'];

              //get other user post
              $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
              WHERE a.id_post = up.id_post
              AND up.id_user = b.id_user
              AND a.status >='".$status."' 
              AND a.id_post=".$row['id_post']."
              AND up.status = 1
              AND b.status >=1 ");
              $result2 = $query2->getResultArray();
              $row['other_users'] =  $result2;

            //get request user post
            $query20   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
            WHERE a.id_post = up.id_post
            AND up.id_user = b.id_user
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status IN (1,3)
            AND b.status=1 ");
            $result20 = $query20->getResultArray();
            $row['request_users'] =  $result20;
            
            //get comment by idpost
            $query3   = $this->query(" SELECT b.* FROM tb_comment b
            WHERE b.status='".$status."' 
            AND b.id_post='".$row['id_post']."' ORDER BY b.id_comment DESC LIMIT 0,10 ");
            $result3 = $query3->getResultArray();
            
            $arrComments = array();
            foreach ($result3 as $rowComm) {
                $queryUser   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                    WHERE b.id_install=c.id_install 
                    AND b.id_user='".$rowComm['id_user']."' ");
                $resultUser = $queryUser->getResultArray();
                $rowComm['user'] =  $resultUser[0];
                $arrComments[] = $rowComm;
            }
            $row['comments'] = $arrComments;

            $return_array[] = $row;
        }
        
        return $return_array;
        /*return $this->where('id_user', $idUser)
                    ->where('status', $status)
                    ->orderBy('id_post','desc')
                    ->findAll($limit, $offset);*/
    }

    public function getAllByIdCateg($idCateg, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $sql = " SELECT a.* FROM tb_post a, tb_liked b 
        WHERE a.id_category=b.id_category
        AND b.flag=2 AND b.status=1  
        AND b.is_liked=1 
        AND a.status='".$status."' 
        AND a.id_category='".$idCateg."'
        ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
        LIMIT ".$getlimit." ";

        $query   = $this->query($sql);


        $results = $query->getResultArray();
        $return_array = array();
        $i = 0;
        foreach ($results as $row) {
            $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c WHERE b.id_install=c.id_install 
                AND  b.id_user='".$row['id_user']."' ");
            $result1 = $query1->getResultArray();
            $row['user'] =  $result1[0];

            $query0   = $this->query(" SELECT a.is_liked FROM tb_liked a WHERE a.id_post='".$row['id_post']."'  
                AND a.id_user='".$row['id_user']."' ");
            $result0 = $query0->getResultArray();
            $row['is_liked'] =  $result0[0]['is_liked'];
            
           //get other user post
           $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
           WHERE a.id_post = up.id_post
           AND up.id_user = b.id_user
           AND a.status >='".$status."' 
           AND a.id_post=".$row['id_post']."
           AND up.status = 1
           AND b.status >=1 ");
           $result2 = $query2->getResultArray();
           $row['other_users'] =  $result2;

            //get request user post
            $query20   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
            WHERE a.id_post = up.id_post
            AND up.id_user = b.id_user
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status IN (1,3)
            AND b.status=1 ");
            $result20 = $query20->getResultArray();
            $row['request_users'] =  $result20;
            
           //get comment by idpost
           $query3   = $this->query(" SELECT b.* FROM tb_comment b
           WHERE b.status='".$status."' 
           AND b.id_post='".$row['id_post']."' ORDER BY b.id_comment DESC LIMIT 0,10 ");
           $result3 = $query3->getResultArray();
           
           $arrComments = array();
            foreach ($result3 as $rowComm) {
                $queryUser   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                    WHERE b.id_install=c.id_install 
                    AND b.id_user='".$rowComm['id_user']."' ");
                $resultUser = $queryUser->getResultArray();
                $rowComm['user'] =  $resultUser[0];
                $arrComments[] = $rowComm;
            }
            $row['comments'] = $arrComments;

            $return_array[] = $row;
        }
        
        return $return_array;
        /*return $this->where('id_user', $idUser)
                    ->where('status', $status)
                    ->orderBy('id_post','desc')
                    ->findAll($limit, $offset);*/
    }

    public function do_liked_post($array) {

        if ($array['iu']!='' && $array['ip'] != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];

            $data = [
                'id_user'       => $idUser,
                'id_post'       => $idPost,
                'is_liked'      => 1,
                'flag'          => 1,
            ];

            //update user
            //$sqlUpdate1 = " UPDATE tb_user SET total_like=total_like+1 WHERE id_user='".$idUser."' ";

            //update post
            //$sqlUpdate2 = " UPDATE tb_post SET total_like=total_like+1 WHERE id_post='".$idPost."' ";
            
            $idLiked = '';
            $check1 = $this->getByIdUserPost($idUser, $idPost);
            if ($check1['id_liked'] != '') {
                $idLiked = $check1['id_liked'];

                $data['id_liked'] = $idLiked;
                $data['is_liked'] = $check1['is_liked'] == '1' ?  0 : 1;

                //update user
                //$sqlUpdate1 = " UPDATE tb_user SET total_like=total_like-1 WHERE id_user='".$idUser."' ";

                //update post
                //$sqlUpdate2 = " UPDATE tb_post SET total_like=total_like-1 WHERE id_post='".$idPost."' ";
            }

            $this->save($data);

            //$this->query($sqlUpdate1);
            //$this->query($sqlUpdate2);

        }

        return $this->getByIdUserPost($idUser, $idPost);
    }

    public function do_liked_category($array) {
        // like or dislike category
        if ($array['iu']!='' && $array['ic'] != '') {
            $idUser = $array['iu'];
            $idCategory = $array['ic'];

            $data = [
                'id_user'       => $idUser,
                'id_category'   => $idCategory,
                'is_liked'      => 1,
                'flag'          => 2,
            ];

            //update user
            $sqlUpdate1 = " UPDATE tb_user SET total_like=total_like+1 WHERE id_user='".$idUser."' ";

            //update category
            $sqlUpdate2 = " UPDATE tb_category SET total_like=total_like+1, total_interest=total_interest+1 WHERE id_category='".$idCategory."' ";
            
            $idLiked = '';
            $check1 = $this->getByIdUserCateg($idUser, $idCategory);
            if ($check1['id_liked'] != '') {
                $data['id_liked'] = $check1['id_liked'];

                $isLiked = $check1['is_liked'] == '1' ? 0 : 1;
                $data['is_liked'] = $isLiked;

                if ($isLiked == 0) {    
                    //update user
                    $sqlUpdate1 = " UPDATE tb_user SET total_like=total_like-1 WHERE id_user='".$idUser."' ";

                    //update category
                    $sqlUpdate2 = " UPDATE tb_category SET total_like=total_like-1, total_interest=total_interest-1 WHERE id_category='".$idCategory."' ";
                }
            }

            $this->save($data);

            $this->query($sqlUpdate1);
            $this->query($sqlUpdate2);

        }

        return $this->getByIdUserCateg($idUser, $idCategory);
    }

    public function getById($id) {
        return $this->where('id_liked', $id)
                    ->first();
    }

    public function getByIdUserPost($idUser, $idPost) {
        return $this->where('id_user', $idUser)
                    ->where('id_post', $idPost)
                    ->first();
    }

    public function getByIdUserCateg($idUser, $idCateg) {
        return $this->where('id_user', $idUser)
                    ->where('id_category', $idCateg)
                    ->where('flag', '2')
                    ->first();
    }
    
}