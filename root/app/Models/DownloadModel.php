<?php

namespace App\Models;

use CodeIgniter\Model;

class DownloadModel extends Model
{
    protected $table      = 'tb_download';
    protected $primaryKey = 'id_download';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_post', 'id_user', 'url', 'path', 'size', 'mime_type', 'flag', 'status', 'date_created', 'date_updated'];
    
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    protected $skipValidation     = true;

    public function getTotal() {
        $query   = $this->query(" SELECT count(id_download) as total FROM tb_download ");
        $results = $query->getResultArray();
        return $results;
    }

    public function allByLimitPanel($limit=100, $offset=0) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.*, 
            (SELECT x.fullname FROM tb_user x WHERE x.id_user=a.id_user) as fullname,
            (SELECT x.total_view FROM tb_post x WHERE x.id_post=a.id_post) as total_view,
            (SELECT x.total_like FROM tb_post x WHERE x.id_post=a.id_post) as total_like,
            (SELECT x.total_download FROM tb_post x WHERE x.id_post=a.id_post) as total_download
            FROM tb_download a 
            ORDER BY a.id_download DESC
            LIMIT ".$getlimit." ");

        return $query->getResultArray();
    }

    public function allByLimit($limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_download a 
            WHERE a.status='".$status."' 
            ORDER BY a.id_download DESC, a.url ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {

            //get post
            $query1   = $this->query(" SELECT b.* FROM tb_post b
            WHERE b.id_post=".$row['id_post']."
            AND b.status=1 ");
            $result1 = $query1->getResultArray();
            $row['post'] =  $result1[0];
            
            //get user
            $query2   = $this->query(" SELECT b.* FROM tb_user b
            WHERE b.id_user=".$row['id_user']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['user'] =  $result2[0];
            
            $return_array[] = $row;
        }

        //print_r($return_array);
        //die();

        
        return $return_array;

        /*return $this->where('status','1')
                    ->orderBy('total_interest','desc')
                    ->orderBy('title','asc')
                    ->findAll($limit, $offset);*/
    }

    public function allByLimitByIdUser($idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_download a 
            WHERE a.status='".$status."'
            AND a.id_user='".$idUser."' 
            ORDER BY a.id_download DESC, a.url ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {

            //get post
            $query1   = $this->query(" SELECT b.* FROM tb_post b
            WHERE b.id_post=".$row['id_post']."
            AND b.status=1 ");
            $result1 = $query1->getResultArray();
            $row['post'] =  $result1[0];
            
            //get user
            $query2   = $this->query(" SELECT b.* FROM tb_user b
            WHERE b.id_user=".$row['id_user']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['user'] =  $result2[0];
            
            $return_array[] = $row;
        }

        //print_r($return_array);
        //die();

        
        return $return_array;
    }

    public function allByLimitByIdPost($idPost, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $query   = $this->query(" SELECT a.* FROM tb_download a 
            WHERE a.status='".$status."'
            AND a.id_post='".$idPost."' 
            ORDER BY a.id_download DESC, a.url ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        $return_array = array();

        $i = 0;
        foreach ($results as $row) {

            //get post
            $query1   = $this->query(" SELECT b.* FROM tb_post b
            WHERE b.id_post=".$row['id_post']."
            AND b.status=1 ");
            $result1 = $query1->getResultArray();
            $row['post'] =  $result1[0];
            
            //get user
            $query2   = $this->query(" SELECT b.* FROM tb_user b
            WHERE b.id_user=".$row['id_user']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['user'] =  $result2[0];
            
            $return_array[] = $row;
        }

        //print_r($return_array);
        //die();

        
        return $return_array;
    }

    public function saveUpdate($array) {
        if ($array['iu'] != '' && $array['ip'] != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];

            $data = [
                "id_user"       => $idUser,
                "id_post"       => $idPost,
                "url"           => $array['url'],
                "path"          => $array['pt'],
                "size"          => $array['sz'],
                "mime_type"     => $array['mt'],
                "status"        => $array['st']
            ];

            
            $checkExist = $this->getByIdUserPost($idUser, $idPost);
            if ($checkExist['id_download'] != '') {
                $data['id_download'] = $checkExist['id_download'];
                $data["status"] = 1;
            }

            $this->save($data);
        }
    }

    public function getById($id) {
        return $this->where('id_download', $id)
                    ->first();
    }

    public function getByIdUser($idUser) {
        return $this->where('id_user', $idUser)
                    ->first();
    }

    public function getByIdUserPost($idUser, $idPost) {
        return $this->where('id_user', $idUser)
                    ->where('id_post', $idPost)
                    ->first();
    }
}