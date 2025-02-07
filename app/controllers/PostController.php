<?php
require_once '../app/models/PostModel.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class PostController {
    private $model;
    private $secretKey;

    public function __construct($pdo) {
        $this->model = new PostModel($pdo);
        $this->secretKey = 'hellothisismysecretkeyforthinktogether'; 
    }

    // Get all posts
    // public function getAllPosts() {
    //     header('Content-Type: application/json');  
    //     try {
    //         $posts = $this->model->getAllPosts();
    //         echo json_encode($posts);
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(["error" => "Failed to fetch posts: " . $e->getMessage()]);
    //     }
    // }
    
    public function getAllPosts() {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $sortBy = $_GET['sortBy'] ?? '';
    
        $posts = $this->model->getAllPosts($search, $category, $sortBy);
        echo json_encode($posts);
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
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? '';
            
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                error_log("Authorization header missing or invalid.");
                http_response_code(401);
                echo json_encode(["error" => "Unauthorized"]);
                return;
            }
    
            $jwt = $matches[1];
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
    
            $userId = $decoded->data->id ?? null;
            if (!$userId) {
                error_log("User ID not found in token.");
                http_response_code(401);
                echo json_encode(["error" => "User ID not found in token"]);
                return;
            }
    
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                error_log("Invalid request data: " . file_get_contents("php://input"));
                http_response_code(400);
                echo json_encode(["error" => "Invalid request data"]);
                return;
            }
    
            error_log("Post data: " . print_r($data, true));
    
            $data['userId'] = $userId;
    
            $id = $this->model->createPost($data);
            http_response_code(201);
            echo json_encode(["id" => $id]);
    
        } catch (Exception $e) {
            error_log("Create Post Exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Failed to create post", "details" => $e->getMessage()]);
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
