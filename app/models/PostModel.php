<?php
class PostModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Fetch all posts without numeric keys
    public function getAllPosts() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM Post");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Debugging: Show the error
            echo "Database Error: " . $e->getMessage();
            exit;
        }
    }

    // ✅ Fetch a single post without numeric keys
    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Post WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Changed here
    }

    public function getCommentsByPostId($postId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Comment WHERE PostId = ?");
            $stmt->execute([$postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Debugging: Show the error
            echo "Database Error: " . $e->getMessage();
            exit;
        }
    }
    
    

    public function createPost($data) {
        $sql = "INSERT INTO Post (UserId, Title, Description, CategoryId, Link, CodeSnippet) 
                VALUES (:userId, :title, :description, :categoryId, :link, :codeSnippet)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'userId' => $data['userId'],
            'title' => $data['title'],
            'description' => $data['description'],
            'categoryId' => $data['categoryId'],
            'link' => $data['link'],
            'codeSnippet' => $data['codeSnippet']
        ]);
        return $this->pdo->lastInsertId();
    }
    

    public function updatePost($id, $data) {
        $sql = "UPDATE Post 
                SET Title = :title, Description = :description, CategoryId = :categoryId, Link = :link, CodeSnippet = :codeSnippet 
                WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    public function deletePost($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Post WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>
