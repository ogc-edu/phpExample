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
$conn = $database->conn;

$headers = getallheaders();
if (isset($headers['Authorization'])) {
  $authHeader = $headers['Authorization'];
  $jwt = str_replace("Bearer ", "", $authHeader); // Remove "Bearer " prefix
  if (!Authenticate::verifyJWT($jwt)) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid JWT token']);
    exit;
  }
} else {
  http_response_code(201);
  echo json_encode(['message' => 'No JWT token detected']);
  exit;
}
$userID = $_REQUEST['userID'] ?? null;    //null first, check at end, maybe set error if really needed
$username = $_REQUEST['username'] ?? null;
$role = $_REQUEST['role'] ?? null;

//Create Competition by admin 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_competition') {
  $name = $_POST['title'];
  $description = $_POST['description'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $voting_end_date = $_POST['voting_end_date'];

  $name = $conn->real_escape_string($name);
  $description = $conn->real_escape_string($description);
  $start_date = $conn->real_escape_string($start_date);
  $end_date = $conn->real_escape_string($end_date);
  $voting_end_date = $conn->real_escape_string($voting_end_date);

  $result = Competition::createCompetition($name, $description, $start_date, $end_date, $voting_end_date, $database);
  if ($result) {
    http_response_code(201);
    echo json_encode(['message' => 'Competition created successfully', 'competition_id' => $result]);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to create competition']);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_competition') {
  $competition_id = $_POST['competition_id'];
  $title = $_POST['title'];
  $description = $_POST['description'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $voting_end_date = $_POST['voting_end_date'];

  $title = $conn->real_escape_string($title);
  $description = $conn->real_escape_string($description);
  $start_date = $conn->real_escape_string($start_date);
  $end_date = $conn->real_escape_string($end_date);
  $voting_end_date = $conn->real_escape_string($voting_end_date);

  $result = Competition::updateCompetition($competition_id, $title, $description, $start_date, $end_date, $voting_end_date, $database);
  if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Competition updated successfully']);
  } else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to update competition']);
  }
}
