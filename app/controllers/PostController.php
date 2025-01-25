<?php
require_once '../app/models/PostModel.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class PostController {
    private $model;
    private $secretKey;

    public function __construct($pdo) {
        $this->secretKey = 'hellothisismysecretkeyforthinktogether'; 
    }

    // Get all posts
    public function getAllPosts() {
        header('Content-Type: application/json');  
        try {
            $posts = $this->model->getAllPosts();
            echo json_encode($posts);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to fetch posts: " . $e->getMessage()]);
        }
    }

    // Get a single post by ID
    public function getPostById($id) {
        header('Content-Type: application/json');  
        try {
            $post = $this->model->getPostById($id);
            if ($post) {
                echo json_encode($post);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Post not found"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to fetch post: " . $e->getMessage()]);
        }
    }

    // Get all comments for a post
    public function getCommentsByPostId($postId) {
        if (!$postId) {
            http_response_code(400);
            echo json_encode(["error" => "Post ID is required"]);
            exit;
        }
    
        try {
            $comments = $this->model->getCommentsByPostId($postId);
            echo json_encode($comments);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to fetch comments", "details" => $e->getMessage()]);
        }
    }
    
    

    // Create a new post
    public function createPost() {
        header('Content-Type: application/json');
        try {
            // Get the Authorization header and decode the JWT
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? '';
            
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                http_response_code(401);
                echo json_encode(["error" => "Unauthorized"]);
                return;
            }
    
            $jwt = $matches[1];
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
    
            // Extract the user ID from the token
            $userId = $decoded->data->id ?? null;
            if (!$userId) {
                http_response_code(401);
                echo json_encode(["error" => "User ID not found in token"]);
                return;
            }
    
            // Parse the incoming request data
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid request data"]);
                return;
            }
    
            // Add the user ID to the post data
            $data['userId'] = $userId;
    
            // Insert the post into the database
            $id = $this->model->createPost($data);
            http_response_code(201);
            echo json_encode(["id" => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create post: " . $e->getMessage()]);
        }
    }
    

    // Update a post
    public function updatePost($id) {
        header('Content-Type: application/json');  
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $rowCount = $this->model->updatePost($id, $data);
            echo json_encode(["updated" => $rowCount > 0]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update post: " . $e->getMessage()]);
        }
    }

    // Delete a post
    public function deletePost($id) {
        header('Content-Type: application/json');  
        try {
            $rowCount = $this->model->deletePost($id);
            echo json_encode(["deleted" => $rowCount > 0]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete post: " . $e->getMessage()]);
        }
    }
}
