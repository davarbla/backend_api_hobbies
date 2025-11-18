<?php

namespace App\Controllers;

use App\Models\AuthHeaderModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TestAuth extends BaseController
{
    public function testHeaders()
    {
        $headers = $this->request->headers();
        
        $authkey = '';
        if (isset($headers['X-Authentication'])) {
            $authkey = (string) $headers['X-Authentication'];
        } else if (isset($headers['x-authentication'])) {
            $authkey = (string) $headers['x-authentication'];
        }
        
        $arr_token = explode(" ", $authkey);
        
        $response = [
            'all_headers' => $headers,
            'x_authentication_raw' => $authkey,
            'exploded_tokens' => $arr_token,
            'token_count' => count($arr_token),
            'token_0' => isset($arr_token[0]) ? $arr_token[0] : 'NOT SET',
            'token_1' => isset($arr_token[1]) ? $arr_token[1] : 'NOT SET',
            'expected_token' => 'ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo',
            'tokens_match' => isset($arr_token[1]) && $arr_token[1] == 'ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
        ];
        
        return $this->response->setJSON($response);
    }
}
