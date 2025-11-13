<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Test extends ResourceController
{
    public function index()
    {
        return $this->respond([
            'status' => 'success',
            'message' => 'API is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function echo()
    {
        $data = $this->request->getJSON(true) ?? [];
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Data received',
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function login()
    {
        $data = $this->request->getJSON(true) ?? [];
        
        // Simple authentication check
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            return $this->failValidationErrors([
                'message' => 'Email and password are required'
            ]);
        }
        
        // In a real application, you would validate against a database
        // This is just a test implementation
        if ($email === 'test@example.com' && $password === 'password') {
            return $this->respond([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => 1,
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                        'token' => 'test_token_1234567890'
                    ]
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $this->failUnauthorized('Invalid email or password');
    }
}
