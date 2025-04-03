<?php
require_once '../models/Votes.php';
require_once '../models/Comments.php';
require_once '../database.php';
require_once '../auth/authenticate.php';
require_once '../models/Competition.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow frontend domain, if front-end on port 3000 local host, edit if other ports
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed headers
header("Access-Control-Allow-Credentials: true"); // Allow cookies/auth headers

$database = new Database("localhost", "root", "", "recipedatabase");
if (!$database->conn) {
  http_response_code(500);
  echo json_encode(['message' => 'Failed to connect to database']);
  exit;
}

// $headers = getallheaders();
// if (isset($headers['Authorization'])) {
//   $authHeader = $headers['Authorization'];
//   $jwt = str_replace("Bearer ", "", $authHeader); // Remove "Bearer " prefix
//   if (!Authenticate::verifyJWT($jwt)) {
//     http_response_code(401);
//     echo json_encode(['message' => 'Invalid JWT token']);
//     exit;
//   }
// } else {
//   http_response_code(201);
//   echo json_encode(['message' => 'No JWT token detected']);
//   exit;
// }
$userID = $_REQUEST['userID'] ?? null;    //null first, check at end, maybe set error if really needed
$username = $_REQUEST['username'] ?? null;
$role = $_REQUEST['role'] ?? null;

//Check if competition is over, run every time when user requests   
$activeCompetitions = Competition::getAllCompetitions($database, true);   //get all active competitions
foreach ($activeCompetitions as $competition) {
  if (date('Y-m-d') >= $competition['end_date']) {
    Competition::setCompetitionToInactive($competition['id'], $database);   //set competition to inactive when end time is over
  }
}

//Get all competitions by pagination
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_competitions_pagination') {
  $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;  //Which page to view, (pageNum * perPage = offset)
  $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;  //default 10 per page, can change in api call
  $active = isset($_GET['active']) ? (int) $_GET['active'] : 1;  //default active competitions(viewing current ongoing competition)
  $result = Competition::getCompetitionsWithPagination($page, $perPage, $database, $active);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Competitions fetched successfully', 'data' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch competitions']);
  }
}

//Get competition by id, when click on specific competition card with competition id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_competition_by_id') {
  $id = $_GET['competition_id'];  //in React card created with {key: competition_id}
  $result = Competition::getCompetitionById($id, $database);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Competition fetched successfully', 'data' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch competition']);
  }
}

//After click on specific competition card, show all recipes in that competition
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_competition_recipes') {
  $competition_id = $_GET['competition_id'];
  $result = Competition::getCompetitionRecipes($competition_id, $database);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Competition recipes fetched successfully', 'data' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch competition recipes']);
  }
}

//After showing all recipes in a competition, user can vote for their favorite recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote_recipe') {
  $recipe_id = $_POST['recipe_id'];
  $user_id = $_POST['user_id'];
  $competition_id = $_POST['competition_id'];
  $result = Votes::voteRecipe($recipe_id, $user_id, $competition_id, $database);
  if ($result) {
    http_response_code(201);
    echo json_encode(['message' => 'Recipe voted successfully']);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to vote recipe']);
  }
}

//After user voted for a recipe, show all votes for that recipe
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_recipe_votes') {
  $recipe_id = $_GET['recipe_id'];
  $competition_id = $_GET['competition_id'];
  $result = Votes::getVotes($recipe_id, $competition_id, $database);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Recipe votes fetched successfully', 'data' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch recipe votes']);
  }
}

//After clicking into a recipe under a competition, user can view comment by that recipe
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_recipe_comments') {
  $recipe_id = $_GET['recipe_id'];
  $competition_id = $_GET['competition_id'];
  $result = Comments::getComments($recipe_id, $competition_id, $database);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Recipe comments fetched successfully', 'data' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch recipe comments']);
  }
}

//User can add comment to a recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
  $recipe_id = $_POST['recipe_id'];
  $user_id = $_POST['user_id'];
  $comment = $_POST['comment'];
  $competition_id = $_POST['competition_id'];
  $result = Comments::addComment($recipe_id, $user_id, $comment, $competition_id, $database);
  if ($result) {
    http_response_code(201);
    echo json_encode(['message' => 'Comment added successfully']);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to add comment']);
  }
}

//Submit recipe to a competition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_recipe') {
  $competition_id = $_POST['competition_id'];
  $recipe_id = $_POST['recipe_id'];
  $result = Competition::enterRecipe($competition_id, $recipe_id, $database);
  if ($result) {
    http_response_code(201);
    echo json_encode(['message' => 'Recipe submitted successfully']);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to submit recipe']);
  }
}



