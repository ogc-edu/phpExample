<?php
require_once '../database.php';
class Comments
{
  public static function addComment($recipe_id, $user_id, $comment, $competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "INSERT INTO comments (recipe_id, user_id, comment, competition_id) VALUES ($recipe_id, $user_id, '$comment', $competition_id)";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }

  public static function getComments($recipe_id, $competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.recipe_id = $recipe_id AND c.competition_id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function deleteComment($comment_id, $database)
  {   //only admin can delete comment, api check only admin can run this function
    $conn = $database->conn;
    $sql = "DELETE FROM comments WHERE id = $comment_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }
}
