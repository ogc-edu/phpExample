<?php
require_once '../database.php';
class Competition
{
  public static function createCompetition($title, $description, $start_date, $end_date, $voting_end_date, $database)
  {
    $conn = $database->conn;
    $title = $conn->real_escape_string($title);
    $description = $conn->real_escape_string($description);
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date = date('Y-m-d', strtotime($end_date));
    $voting_end_date = date('Y-m-d', strtotime($voting_end_date));
    $today = date('Y-m-d');
    $active = ($today >= $start_date && $today <= $end_date) ? 1 : 0;

    $sql = "INSERT INTO competitions (title, description, start_date, end_date, voting_end_date, active) VALUES ('$title', '$description', '$start_date', '$end_date', '$voting_end_date', '$active')";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($conn));
    }
    return $conn->insert_id;
  }

  public static function getAllCompetitions($database, $active)
  {
    $conn = $database->conn;
    if ($active) {     //select active competitions || past competitions wihtout using Active column(to set active to 0 when competition is over)
      $sql = "SELECT * FROM competitions WHERE start_date <= CURDATE() AND end_date >= CURDATE()";
    } else {
      $sql = "SELECT * FROM competitions WHERE end_date < CURDATE()";
    }
    $result = $conn->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function getCompetitionActiveStatus($competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "SELECT active FROM competitions WHERE id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function setCompetitionToInactive($competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "UPDATE competitions SET active = 0 WHERE id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($conn));
    }
    return $result;
  }

  public static function getCompetitionById($id, $database)
  {
    $conn = $database->conn;
    $sql = "SELECT * FROM competitions WHERE id = $id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  //update competition all details
  public static function updateCompetition($id, $title, $description, $start_date, $end_date, $voting_end_date, $database)
  {
    $conn = $database->conn;
    $sql = "UPDATE competitions SET title = '$title', description = '$description', start_date = '$start_date', end_date = '$end_date', voting_end_date = '$voting_end_date' WHERE id = $id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result; //if true return 1, api will return 200
  }

  //delete competition
  public static function deleteCompetition($id, $database)
  {
    $conn = $database->conn;
    $sql = "DELETE FROM competitions WHERE id = $id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }

  //get all competitions with pagination
  public static function getCompetitionsWithPagination($page, $perPage, $database, $active)    //in api call, set total page by sql count 
  {
    $conn = $database->conn;
    $offset = ($page - 1) * $perPage;
    if ($active) {     //select active competitions || past competitions
      $sql = "SELECT * FROM competitions WHERE start_date <= CURDATE() AND end_date >= CURDATE() LIMIT $offset, $perPage";
    } else {
      $sql = "SELECT * FROM competitions WHERE end_date < CURDATE() LIMIT $offset, $perPage";
    }

    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function enterRecipe($competition_id, $recipe_id, $database)
  {
    $conn = $database->conn;

    $competition = self::getCompetitionById($competition_id, $database);
    if (!$competition || $competition[0]['active'] == 0) {
      return false;   //no competition found or not active
    }

    $sql = "INSERT INTO competition_recipes (competition_id, recipe_id) VALUES ($competition_id, $recipe_id)";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $result;
  }

  public static function getCompetitionRecipes($competition_id, $database)
  {   //get all recipes in a competition
    $conn = $database->conn;
    $sql = "SELECT r.*, cr.votes FROM recipes r JOIN competition_recipes cr ON r.id = cr.recipe_id WHERE cr.competition_id = $competition_id";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);
  }

  public static function getWinner($competition_id, $database)
  {
    $conn = $database->conn;
    $sql = "SELECT r.*, cr.votes FROM recipes r JOIN competition_recipes cr ON r.id = cr.recipe_id WHERE cr.competition_id = $competition_id ORDER BY cr.votes DESC LIMIT 1";
    $result = $conn->query($sql);
    if (!$result) {
      die("Query error" . mysqli_error($conn));
    }
    return $database->fetchAll($result);  //return winner recipe
  }
}
