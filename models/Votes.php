<?php
require_once '../database.php';
class Votes
{
  public static function voteRecipe($recipe_id, $user_id, $competition_id, $database)//in api check first if user voted this recipe, if not then vote(run this function)
  {
    $compStatus = Competition::getCompetitionActiveStatus($competition_id, $database);
    if ($compStatus[0]['active'] == 0) {    //cannot vote for inactive competition
      return false;
    }
    $conn = $database->conn;
    $sql = "INSERT INTO votes (recipe_id, user_id) VALUES ($recipe_id, $user_id)";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }

  //once render specific competition, check did user vote any recipes in this competition
  public static function checkUserVoteAll($user_id, $database, $competition_id)
  {    //when click into competition check did user vote any recipes in this competition
    $conn = $database->conn;
    $sql = "SELECT recipe_id FROM votes WHERE user_id = $user_id AND competition_id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function removeVote($recipe_id, $user_id, $database, $competition_id)
  {
    $compStatus = Competition::getCompetitionActiveStatus($competition_id, $database);
    if ($compStatus[0]['active'] == 0) {    //cannot remove vote from inactive competition
      return false;
    }
    $conn = $database->conn;
    $sql = "DELETE FROM votes WHERE recipe_id = $recipe_id AND user_id = $user_id AND competition_id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }

  public static function getVotes($recipe_id, $competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "SELECT COUNT(*) FROM votes WHERE recipe_id = $recipe_id AND competition_id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }
}
