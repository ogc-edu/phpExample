<?php
require_once 'authenticate.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $user_id = $_GET['user_id'];
  $username = $_GET['username'];
  $role = $_GET['role'];

  $jwt = Authenticate::generateJWT($user_id, $username, $role);
  echo json_encode(['jwt' => $jwt]);
}