<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table      = 'tb_feedback';
    protected $primaryKey = 'id_feedback';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_user', 'desc_feedback', 'rating', 'flag', 'status', 'date_created', 'date_updated'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';

    protected $skipValidation     = true;

    public function allByLimitPanel($limit=100, $offset=0) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.*, 
            (SELECT x.fullname FROM tb_user x WHERE x.id_user=a.id_user) as fullname,
            (SELECT x.image FROM tb_user x WHERE x.id_user=a.id_user) as image
            FROM tb_feedback a 
            ORDER BY a.id_feedback DESC
            LIMIT ".$getlimit." ");

        return $query->getResultArray();
    }

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

    
    public function getAllByLimit($limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        $sql = " SELECT b.* FROM tb_feedback b 
            WHERE b.status='".$status."' 
            ORDER BY b.id_feedback DESC 
            LIMIT ".$getlimit." ";

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

            $return_array[] = $row;
        }
        
        return $return_array;
    }

    public function do_feedback($array) {

        if ($array['iu']!='' && $array['ds'] != '' && $array['rt'] != '') {
            $idUser = $array['iu'];
            $desc = $array['ds'];
            $rating = $array['rt'];

            $data = [
                'id_user'         => $idUser,
                'rating'          => $rating,
                'desc_feedback'   => $desc,
                'flag'           => 1,
                'status'           => 1,
            ];
            
            $this->save($data);

        }

        return $this->getAllByLimit();
    }

    public function getById($id) {
        return $this->where('id_feedback', $id)
                    ->first();
    }

    
}