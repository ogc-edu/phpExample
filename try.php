<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo "json_encode(['message' => 'Hello World'])";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo "json_encode(['message' => 'Hello World'])";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_competition') {
  $data = json_decode(file_get_contents('php://input'), true);
  $name = $data['name'];
  echo json_encode(['message' => $name]);
}

?>