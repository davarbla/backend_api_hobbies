<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'tb_category';
    protected $primaryKey = 'id_category';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['title', 'description', 'image', 'subscribe_fcm', 'total_interest', 'total_post', 'total_like', 'flag', 'status'];
    
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    protected $skipValidation     = true;

    public function getTotal() {
        $query   = $this->query(" SELECT count(id_category) as total FROM tb_category ");
        $results = $query->getResultArray();
        return $results;
    }

    public function allByLimit($limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_category a 
            WHERE a.status='".$status."' 
            ORDER BY a.total_interest DESC, a.title ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {
            
            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_user_category a, tb_user b
            WHERE a.id_user=b.id_user
            AND a.status='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['users'] =  $result2;
            
            $return_array[] = $row;
        }

        
        return $return_array;
    }

    public function allByLimitPanel($limit=100, $offset=0) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_category a 
            ORDER BY a.id_category DESC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {
            
            //get other user post
            $query2   = $this->query(" SELECT b.* FROM tb_user_category a, tb_user b
            WHERE a.id_user=b.id_user
            AND a.status='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['users'] =  $result2;
            
            $return_array[] = $row;
        }
        
        return $return_array;
    }

    public function getById($id) {
        return $this->where('id_category', $id)
                    ->first();
    }
}