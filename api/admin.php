<?php
require_once '../models/Votes.php';
require_once '../models/Comments.php';
require_once '../database.php';
require_once '../auth/authenticate.php';

$database = new Database("localhost", "root", "", "recipe_competition");

