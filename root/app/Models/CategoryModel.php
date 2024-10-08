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
    
    protected $allowedFields = ['title', 'description', 'image', 'subscribe_fcm', 'total_interest', 'total_post', 'total_like', 'flag', 'status','id_category_up','group','private', 'latitude','location','fun','id_owner','lat','lng','country'];
    
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
            ORDER BY a.date_created DESC, a.title ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {
            
            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.*, c.token_fcm 
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['users'] =  $result2;

            $query3   = $this->query(" SELECT b.*, c.token_fcm
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status>='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result3 = $query3->getResultArray();
            $row['usersPending'] =  $result3;

            $queryUser   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
            WHERE b.id_install=c.id_install 
            AND b.id_user='".$row['id_owner']."' ");
            $resultUser = $queryUser->getResultArray();
            $row['user'] =  $resultUser[0];
            
            $return_array[] = $row;
        }

        
        return $return_array;
    }

    public function allByLimitCountry($limit=100, $offset=0, $country=ZZ, $status=1 ) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_category a 
            WHERE a.status='".$status."' 
            AND a.country='".$country."' 
            ORDER BY a.date_created DESC, a.title ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {
            
            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.*, c.token_fcm 
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status='".$status."'             
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['users'] =  $result2;

            $query3   = $this->query(" SELECT b.*, c.token_fcm
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status>='".$status."'             
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result3 = $query3->getResultArray();
            $row['usersPending'] =  $result3;

            $queryUser   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
            WHERE b.id_install=c.id_install 
            AND b.id_user='".$row['id_owner']."' ");
            $resultUser = $queryUser->getResultArray();
            $row['user'] =  $resultUser[0];
            
            $return_array[] = $row;
        }

        
        return $return_array;
    }

    public function allByLimitPanel($limit=100, $offset=0) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_category a 
            ORDER BY a.date_created DESC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {
            
            //get other user post
            $query2   = $this->query(" SELECT b.*, c.token_fcm   
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['users'] =  $result2;

            $query3   = $this->query(" SELECT b.*, c.token_fcm 
            FROM tb_user_category a, tb_user b, tb_install c
            WHERE a.id_user=b.id_user
            AND b.id_install=c.id_install
            AND a.status>='".$status."' 
            AND a.id_category=".$row['id_category']."
            AND b.status=1 ");
            $result3 = $query3->getResultArray();
            $row['usersPending'] =  $result3;

            $queryUser   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
            WHERE b.id_install=c.id_install 
            AND b.id_user='".$row['id_owner']."' ");
            $resultUser = $queryUser->getResultArray();
            $row['user'] =  $resultUser[0];
            
            $return_array[] = $row;
        }
        
        return $return_array;
    }

    public function getById($id) {
        return $this->where('id_category', $id)
                    ->first();
    }
}