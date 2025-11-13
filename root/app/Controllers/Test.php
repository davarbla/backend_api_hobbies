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

    public function register()
    {
        $data = $this->request->getJSON(true) ?? [];
        
        // Basic validation
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $password_confirmation = $data['password_confirmation'] ?? '';
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($password) || empty($password_confirmation)) {
            return $this->failValidationErrors([
                'message' => 'All fields are required',
                'errors' => [
                    'name' => empty($name) ? 'Name is required' : '',
                    'email' => empty($email) ? 'Email is required' : '',
                    'password' => empty($password) ? 'Password is required' : '',
                    'password_confirmation' => empty($password_confirmation) ? 'Please confirm your password' : ''
                ]
            ]);
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors([
                'message' => 'Invalid email format',
                'errors' => [
                    'email' => 'Please enter a valid email address'
                ]
            ]);
        }
        
        // Validate password match
        if ($password !== $password_confirmation) {
            return $this->failValidationErrors([
                'message' => 'Passwords do not match',
                'errors' => [
                    'password' => 'Passwords do not match',
                    'password_confirmation' => 'Passwords do not match'
                ]
            ]);
        }
        
        // Validate password strength (min 6 characters)
        if (strlen($password) < 6) {
            return $this->failValidationErrors([
                'message' => 'Password must be at least 6 characters',
                'errors' => [
                    'password' => 'Password must be at least 6 characters'
                ]
            ]);
        }
        
        // In a real application, you would:
        // 1. Check if email already exists
        // 2. Hash the password
        // 3. Save to database
        
        // For this test implementation, we'll just return a success response
        return $this->respond([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => rand(1000, 9999), // Random ID for testing
                    'name' => $name,
                    'email' => $email,
                    'token' => 'test_token_' . bin2hex(random_bytes(16))
                ]
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ], 201); // 201 Created
    }
}
