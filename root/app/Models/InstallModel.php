<?php

namespace App\Models;

use CodeIgniter\Model;

class InstallModel extends Model
{
    protected $table      = 'tb_install';
    protected $primaryKey = 'id_install';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = ['token_fcm', 'uuid', 'token_forgot', 'flag', 'status', 'os_platform', 'date_created', 'date_updated'];

    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
    protected $updatedField  = 'date_updated';

    protected $skipValidation  = true;

    public function getTotal() {
        $query   = $this->query(" SELECT count(id_install) as total FROM tb_install ");
        $results = $query->getResultArray();
        return $results;
    }

    public function allByLimit($limit=100, $offset=0) {
        return $this->where('status','1')
                    ->orderBy('id_install','desc')
                    ->findAll($limit, $offset);
    }

    public function getByToken($token) {
        return $this->where('token_fcm', $token)
                    ->first();
    }

    public function getById($id) {
        return $this->where('id_install', $id)
                    ->first();
    }

    public function saveUpdate($array) {
        $data = [
            'id_install'   => $array['id'],
            'token_fcm'    => $array['tk'],
            'uuid'         => $array['uuid'],
            'os_platform'  => $array['os']
        ];

        $check = $this->getByToken($array['tk']);

        if ($check['id_install'] != '' && $check['id_install'] != '0') { 
            $data['id_install'] = $check['id_install'];
        }
        $this->save($data);

        return $this->getByToken($array['tk']);
    }
}

/*
	id_install, token_fcm, uuid, os_platform, 
    token_forgot, timestamp, flag, status, date_created, date_updated
*/