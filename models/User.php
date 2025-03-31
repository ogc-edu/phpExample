<?php
require_once '../database.php';
require_once '../auth/authenticate.php';
class User
{
  public static function register($username, $email, $passowrd, $database)
  {
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$passowrd')";
    $result = $database->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($database->conn));
    }
    return $database->getLastInsertId();    //return user id
  }

  public static function login($username, $password, $database)
  {
    $password = password_hash($password, PASSWORD_DEFAULT);   //verify password
    $sql = "SELECT user_id, username, role FROM users WHERE username = '$username' AND password = '$password'";
    $result = $database->query($sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($database->conn));
    }
    if (mysqli_num_rows($result) == 0) {
      return false;   //if no user found return false
    }
    $user[] = mysqli_fetch_assoc($result);
    //if run login means user JWT expired or not set, either way just set new JWT
    setcookie('JWT', Authenticate::generateJWT($user['user_id'], $user['username'], $user['role']), time() + 3600, '/', '', true, true);
    return $user;    //return user info after succesfully login and JWT generation(cookie)
  }
}
