<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table      = 'tb_comment';
    protected $primaryKey = 'id_comment';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['id_post', 'id_user', 'title', 'description', 'latitude', 'rating', 'flag', 'status', 'date_created', 'date_updated'];
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

    public function allByLimitByIdPost($idPost, $limit=100, $offset=0, $status=1) {
        $getlimit = "$offset,$limit";

        $query3   = $this->query(" SELECT b.* FROM tb_comment b
        WHERE b.status='".$status."' 
        AND b.id_post='".$idPost."' ORDER BY b.id_comment DESC LIMIT $getlimit ");
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

        /*return $this->where('status', $status)
                    ->where('id_post', $idPost)
                    ->orderBy('date_created','desc')
                    ->findAll($limit, $offset);*/
    }

    public function do_comment_post($array) {

        if ($array['iu'] != '' && $array['iu'] != '0' && $array['ip'] != '' && trim($array['ds']) != '') {
            $idUser = $array['iu'];
            $idPost = $array['ip'];
            
            //htmlspecialchars(strip_tags(
            $data = [
                'id_user'       => $idUser,
                'id_post'       => $idPost,
                'latitude'      =>  $array['lat'],
                'location'      =>  $array['loc'],
                'description'   => htmlspecialchars(strip_tags($array['ds'])),
            ];

            
            $this->save($data);

            //update user
            $sqlUpdate1 = " UPDATE tb_user SET total_comment=total_comment+1 WHERE id_user='".$idUser."' ";
            $this->query($sqlUpdate1);

            //update post
            $sqlUpdate2 = " UPDATE tb_post SET total_comment=total_comment+1, total_user=total_user+1 WHERE id_post='".$idPost."' ";
            $this->query($sqlUpdate2);

            //update user categ
            $idCateg = $array['ic'];
            $sqlUpdate3 = " UPDATE tb_user_category SET count_comment=count_comment+1, count_interest=count_interest+1 WHERE id_category='".$idCateg."' AND id_user='".$idUser."' ";
            $this->query($sqlUpdate3);

            //update category
            $sqlUpdate4 = " UPDATE tb_category SET total_interest=total_interest+1 WHERE id_category='".$idCateg."' ";
            $this->query($sqlUpdate4);

        }

        return $this->getByIdUserPost($idUser, $idPost);
    }

    public function delete_comment($array) {

        if ($array['id']!='') {
            
            $data = [
                'id_comment'       => $array['id'],
                'id_category'   => $idCategory,
                'is_comment'      => 1,
                'flag'          => 2,
            ];

           
            
            $this->delete($data);

            //update user
            $sqlUpdate1 = " UPDATE tb_user SET total_like=total_like+1 WHERE id_user='".$idUser."' ";
            $this->query($sqlUpdate1);

            //update category
            $sqlUpdate2 = " UPDATE tb_category SET total_like=total_like+1, total_interest=total_interest+1 WHERE id_category='".$idCategory."' ";
            $this->query($sqlUpdate2);

        }

        return $this->getByIdUserPost($idUser, $idPost);
    }

    public function getById($id) {
        return $this->where('id_comment', $id)
                    ->first();
    }

    public function getByIdUserPost($idUser, $idPost) {
        return $this->where('id_user', $idUser)
                    ->where('id_post', $idPost)
                    ->first();
    }

    
}