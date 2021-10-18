<?php

namespace App\Models;

use CodeIgniter\Model;

class FollowModel extends Model
{
    protected $table      = 'tb_follow';
    protected $primaryKey = 'id_follow';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_user', 'id_user_to', 'counter_follow', 'counter_unfollow', 'need_request', 'flag', 'status', 'date_created', 'date_updated'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    //protected $deletedField  = 'deleted_at';

    //protected $validationRules    = [];
    //protected $validationMessages = [];
    protected $skipValidation     = true;

    public function allByLimit($limit=100, $offset=0, $status=1, $flag=1) {
        return $this->where('status', $status)
                    ->where('flag', $flag)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function do_follow($array) {

        if ($array['iu'] !='' && $array['it'] != '') {
            $idUser = $array['iu'];
            $idUserTo = $array['it'];

            $data = [
                'id_user'       => $idUser,
                'id_user_to'       => $idUserTo,
                'flag'  => 1,
            ];

            $idFollow = '';
            $check1 = $this->findFollowByIdUser($idUser, $idUserTo);
            if ($check1['id_follow'] != '') {
                $idFollow = $check1['id_follow'];
                $data['id_follow'] = $idFollow;
                $data['counter_follow'] = $check1['counter_follow'] + 1;
                $data['status'] = '1';
            }

            $this->save($data);

            //update user
            //update following idUser
            $sql = " UPDATE tb_user SET total_following=total_following+1 WHERE id_user='".$idUser."' ";
            $this->query($sql);

            //update follower idUserTo
            $sql = " UPDATE tb_user SET total_follower=total_follower+1 WHERE id_user='".$idUserTo."' ";
            $this->query($sql);

        }

        return $this->getSingleFollowingByIduser($array['iu']);
    }

    public function do_unfollow($array) {

        if ($array['iu']!='' && $array['it'] != '') {
            $idUser = $array['iu'];
            $idUserTo = $array['it'];
            
            $idFollow = '';
            $check1 = $this->findFollowByIdUser($idUser, $idUserTo);
            if ($check1['id_follow'] != '') {
                $idFollow = $check1['id_follow'];
            }
            /*else {
                $check2 = $this->findFollowByIdUser($idUserTo, $idUser);
                if ($check2['id_follow'] != '') {
                    $idFollow = $check2['id_follow'];
                }
            }*/

            if ($idFollow != '') {
                $data = [
                    'id_follow'  => $idFollow,
                    'counter_unfollow' => $check1['counter_unfollow'] + 1,
                    'status' => 0,
                ];

                $this->save($data);

                //update following idUser
                $sql = " UPDATE tb_user SET total_following=total_following-1 WHERE id_user='".$idUser."' ";
                $this->query($sql);

                //update follower idUserTo
                $sql = " UPDATE tb_user SET total_follower=total_follower-1 WHERE id_user='".$idUserTo."' ";
                $this->query($sql);
            }
        }

        return $this->getSingleFollowingByIduser($array['iu']);
    }

    public function getById($id) {
        return $this->where('id_follow', $id)
                    ->first();
    }

    public function findFollowByIdUser($idUser, $idUserTo) {
        return $this->where('flag', 1)
                    ->where('id_user', $idUser)
                    ->where('id_user_to', $idUserTo)
                    ->first();
    }

    public function getSingleFollowingByIduser($idUser) {
        return $this->where('flag', 1)
                    ->where('id_user', $idUser)
                    ->first();
    }

    public function getSingleFollowerByIduser($idUser) {
        return $this->where('flag', 1)
                    ->where('id_user_to', $idUser)
                    ->first();
    }

    public function getAllFollowingByIdUser($idUser, $limit=100, $offset=0, $status=1, $flag=1) {
        $getlimit = "$offset,$limit";

        $sql = " SELECT a.* FROM tb_follow a 
            WHERE a.status='".$status."' 
            AND a.id_user='".$idUser."'
            AND a.flag='".$flag."'
            ORDER BY a.date_created DESC
            LIMIT ".$getlimit." ";

        $query   = $this->query($sql);
        $results = $query->getResultArray();
       
        $return_array = array();
        $i = 0;
        foreach ($results as $row) {
            $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                WHERE b.id_install=c.id_install 
                AND b.id_user='".$row['id_user_to']."' ");
            $result1 = $query1->getResultArray();
            $row['user'] =  $result1[0];

            $return_array[] = $row;
        }
        
        return $return_array;
    }

    public function getFollowingByIdUser($idUser, $limit=100, $offset=0, $status=1, $flag=1) {
        return $this->where('flag', $flag)
                    ->where('status', $status)
                    ->where('id_user', $idUser)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }

    public function getAllFollowerByIdUser($idUser, $limit=100, $offset=0,  $status=1, $flag=1) {
        $getlimit = "$offset,$limit";
        $query   = $this->query(" SELECT a.* FROM tb_follow a 
            WHERE a.status='".$status."' 
            AND a.flag='".$flag."'
            AND a.id_user_to='".$idUser."'
            ORDER BY a.date_created DESC
            LIMIT ".$getlimit." ");


        $results = $query->getResultArray();
        //print_r($results);
        //die();
        $return_array = array();
        $i = 0;
        foreach ($results as $row) {
            $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                WHERE b.id_install=c.id_install 
                AND b.id_user='".$row['id_user']."' ");
            $result1 = $query1->getResultArray();
            $row['user'] =  $result1[0];

            $return_array[] = $row;
        }
        
        return $return_array;
    }

    public function getFollowersByIdUser($idUser, $limit=100, $offset=0,  $status=1, $flag=1) {
        return $this->where('flag', $flag)
                    ->where('status', $status)
                    ->where('id_user_to', $idUser)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);
    }
}