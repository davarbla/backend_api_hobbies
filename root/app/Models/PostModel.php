<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class PostModel extends Model
{
    protected $table      = 'tb_post';
    protected $primaryKey = 'id_post';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['title', 'description', 'id_category', 'id_user', 'latitude', 'location', 'image', 
    'image2', 'image3', 'total_like', 'total_comment', 
    'total_user', 'total_download', 'total_view', 'total_report', 
    'timestamp', 'flag', 'status', 'date_created', 'date_updated'];

    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';
    protected $skipValidation     = true;

    public function getTotal() {
        $query   = $this->query(" SELECT count(id_post) as total FROM tb_post ");
        $results = $query->getResultArray();
        return $results;
    }

    public function allByLimitPanel($limit=100, $offset=0, $status='', $report='') {
        $getlimit = "$offset,$limit";
        
        $sql = " SELECT a.*, b.fullname FROM tb_post a, tb_user b
            WHERE a.id_user=b.id_user 
            ORDER BY a.id_post DESC 
            LIMIT ".$getlimit." ";

        if ($status != '' && $report != '') {
            $sql = " SELECT a.*, b.fullname FROM tb_post a, tb_user b
            WHERE a.id_user=b.id_user 
            AND a.status='".$status."'
            AND a.total_report > 0 
            ORDER BY a.id_post DESC 
            LIMIT ".$getlimit." ";
        }  
        else if ($status != '') {
            $sql = " SELECT a.*, b.fullname FROM tb_post a, tb_user b
            WHERE a.id_user=b.id_user 
            AND a.status='".$status."'
            ORDER BY a.id_post DESC 
            LIMIT ".$getlimit." ";
        }  
        else if ($report != '') {
            $sql = " SELECT a.*, b.fullname FROM tb_post a, tb_user b
            WHERE a.id_user=b.id_user 
            AND a.total_report > 0 
            ORDER BY a.id_post DESC 
            LIMIT ".$getlimit." ";
        }    

        $query   = $this->query($sql);

        $results = $query->getResultArray();
        
        return $results;
    }

    public function do_like($array) {

        if ($array['iu']!='' && $array['ip'] != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];

            $dataPost = $this->getById($idPost);

            $data = [
                'id_post'       => $idPost,
                'total_like'    => $dataPost['total_like'] + 1,
            ];

            $this->save($data);

            //update user
            $sql = " UPDATE tb_user SET total_like=total_like+1 WHERE id_user='".$idUser."' ";
            $this->query($sql);

            //update categ
            $sql = " UPDATE tb_category SET total_like=total_like+1, total_interest=total_interest+1 WHERE id_category='".$dataPost['id_category']."' ";
            $this->query($sql);

            //update user categ
            $sql = " UPDATE tb_user_category SET count_like=count_like+1, count_interest=count_interest+1 WHERE id_category='".$dataPost['id_category']."' AND id_user='".$idUser."' ";
            $this->query($sql);

            return [$dataPost];
        }

        return [];
    }

    public function do_dislike($array) {

        if ($array['iu']!='' && $array['ip'] != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];

            $dataPost = $this->getById($idPost);

            $data = [
                'id_post'       => $idPost,
                'total_like'    => $dataPost['total_like'] - 1,
            ];

            $this->save($data);

            //update user
            $sql = " UPDATE tb_user SET total_like=total_like-1 WHERE id_user='".$idUser."' ";
            $this->query($sql);

            //update categ
            $sql = " UPDATE tb_category SET total_like=total_like-1, total_interest=total_interest-1 WHERE id_category='".$dataPost['id_category']."' ";
            $this->query($sql);

            //update user categ
            $sql = " UPDATE tb_user_category SET count_like=count_like-1, count_interest=count_interest-1 WHERE id_category='".$dataPost['id_category']."' AND id_user='".$idUser."' ";
            $this->query($sql);

            return [$dataPost];
        }

        return [];
    }

    public function do_download($array) {

        if ($array['iu']!='' && $array['ip'] != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];

            $dataPost = $this->getById($idPost);

            $data = [
                'id_post'       => $idPost,
                'total_download'    => $dataPost['total_download'] + 1,
            ];

            $this->save($data);

            //update user
            $sql = " UPDATE tb_user SET total_download=total_download+1 WHERE id_user='".$idUser."' ";
            $this->query($sql);

            //update categ
            $sql = " UPDATE tb_category SET total_interest=total_interest+1 WHERE id_category='".$dataPost['id_category']."' ";
            $this->query($sql);

            //update user categ
            $sql = " UPDATE tb_user_category SET count_interest=count_interest+1 WHERE id_category='".$dataPost['id_category']."' AND id_user='".$idUser."' ";
            $this->query($sql);

            return [$dataPost];
        }

        return [];
    }

    
    public function allByLimit($limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        //print($getlimit);
        //die();
        
        $query   = $this->query(" SELECT a.* FROM tb_post a 
            WHERE a.status='".$status."' 
            ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
            LIMIT ".$getlimit." ");

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
                AND a.id_user='".$row['id_user']."' ");
            $result0 = $query0->getResultArray();
            $row['is_liked'] =  $result0[0]['is_liked'];

            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
            WHERE a.id_post = up.id_post
            AND up.id_user = b.id_user
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
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

        //print_r($return_array);
        //die();

        
        return $return_array;
        /*return $this->where('status', $status)
                    ->orderBy('id_post','desc')
                    ->orderBy('total_like','desc')
                    ->orderBy('total_comment','desc')
                    ->orderBy('title','asc')
                    ->findAll($limit, $offset);*/
    }

    public function allByLimitByIdUser($idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        //print($getlimit);
        //die();
        
        $query   = $this->query(" SELECT a.* FROM tb_post a 
            WHERE a.status='".$status."' 
            ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
            LIMIT ".$getlimit." ");

        $results = $query->getResultArray();
        //print_r($results);
        //die();
        $return_array = array();
        $i = 0;
        foreach ($results as $row) {
            $query1   = $this->query(" SELECT b.*, c.token_fcm FROM tb_user b, tb_install c WHERE b.id_install=c.id_install 
                AND b.id_user='".$row['id_user']."' LIMIT 1 ");
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
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
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
    }

    public function getByIdArray($id, $status=1) {
        $query   = $this->query(" SELECT a.* FROM tb_post a 
            WHERE a.id_post='".$id."' ");

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
                AND a.id_user='".$row['id_user']."' ");
            $result0 = $query0->getResultArray();
            $row['is_liked'] =  $result0[0]['is_liked'];

            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
            WHERE a.id_post = up.id_post
            AND up.id_user = b.id_user
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."     
            AND up.status=1       
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
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

        //print_r($return_array);
        //die();

        
        return $return_array;
    }

    public function saveUpdate($array) {
        //$this->desc_news = htmlspecialchars(strip_tags($this->desc_news));
        
        if ($array['iu'] != '0') {
            $data = [
                'id_post'       => $array['id'],
                'title'         => 'Post_Title', //$array['ds'],
                'description'   => htmlspecialchars(strip_tags($array['ds'])),
                'id_category'   => $array['ic'],
                'id_user'       => $array['iu'],
                'latitude'      => $array['lat'],
                'location'      => $array['loc'],
                'image'         => $array['img'],
            ];
            $this->save($data);
        }

        return $this->getAllByIdUser($array['iu']);
    }

    public function getById($id) {
        return $this->where('id_post', $id)
                    ->first();
    }

    public function getByIdUser($idUser) {
        return $this->where('id_user', $idUser)
                    ->orderBy('id_post','desc')
                    ->first();
    }

    public function searchAllPosts($query, $idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        $sql = " SELECT a.* FROM tb_post a, tb_category b  
            WHERE a.id_category=b.id_category
            AND a.status='".$status."' 
            AND (a.description LIKE '%".$query."%' OR b.title LIKE '%".$query."%' OR b.description LIKE '%".$query."%') 
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
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
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

    public function getAllByIdUser($idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        $query   = $this->query(" SELECT a.* FROM tb_post a 
            WHERE a.status='".$status."' 
            AND a.id_user='".$idUser."'
            ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
            LIMIT ".$getlimit." ");


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
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row[''] =  $result2;
            
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
    }

    public function getAllByIdCateg($idCateg, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $sql = " SELECT a.* FROM tb_post a 
        WHERE a.status='".$status."' 
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
            $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b
            WHERE a.id_user=b.id_user
            AND a.status='".$status."' 
            AND a.id_user != '".$row['id_user']."'
            AND a.id_category='".$idCateg."'
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
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
    }

    public function getAllByIdPost($idPost, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $sql = " SELECT a.* FROM tb_post a 
        WHERE a.status='".$status."' 
        AND a.id_post='".$idPost."' ";

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
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
           //get comment by idpost
           $query3   = $this->query(" SELECT b.* FROM tb_comment b
           WHERE b.status='".$status."' 
           AND b.id_post='".$row['id_post']."' ORDER BY b.id_comment DESC LIMIT $getlimit ");
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
    }

    public function getAllByIdPostIdUser($idPost, $idUser, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";
        
        $sql = " SELECT a.* FROM tb_post a 
        WHERE a.status='".$status."' 
        AND a.id_post='".$idPost."' ";

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
                AND a.id_user='".$idUser."' ");
            $result0 = $query0->getResultArray();
            $row['is_liked'] =  $result0[0]['is_liked'];
            
            //get other user post
            $query2   = $this->query(" SELECT DISTINCT b.* FROM tb_post a, tb_user b, tb_user_post up
            WHERE a.id_post = up.id_post
            AND up.id_user = b.id_user            
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND up.status=1
            AND b.status=1 ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
            
           //get comment by idpost
           $query3   = $this->query(" SELECT b.* FROM tb_comment b
           WHERE b.status='".$status."' 
           AND b.id_post='".$row['id_post']."' ORDER BY b.id_comment DESC LIMIT $getlimit ");
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
    }



    public function getAllByIdUserCateg($idUser, $idCateg, $limit=100, $offset=0, $status=1) {
        //AND a.id_user='".$idUser."'
        $getlimit = "$offset,$limit";
        //print($getlimit);
        $query   = $this->query(" SELECT a.* FROM tb_post a 
            WHERE a.status='".$status."' 
            AND a.id_category='".$idCateg."'
            ORDER BY a.id_post DESC, a.total_like DESC, a.total_comment DESC, a.title ASC 
            LIMIT ".$getlimit." ");


        $results = $query->getResultArray();
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
            AND a.status='".$status."' 
            AND a.id_post=".$row['id_post']."
            AND b.status=1
            AND up.status=1
            AND a.id_category='".$idCateg."' ");
            $result2 = $query2->getResultArray();
            $row['other_users'] =  $result2;
        

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
    }

    public function getAllCommentById($idPost, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";

        //get comment by idpost
        $query3   = $this->query(" SELECT b.* FROM tb_comment b
        WHERE b.status='".$status."' 
        AND b.id_post='".$idPost."' 
        ORDER BY b.id_comment DESC LIMIT $getlimit ");
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
        
        return $arrComments;
    }

    
}

/*
id_post, title, description, id_category, id_user, image, 
image2, image3, total_like, total_comment, 
total_user, total_download, total_view, 
timestamp, flag, status, date_created, date_updated
 */