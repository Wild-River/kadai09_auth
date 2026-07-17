<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

session_destroy();
redirect('login.php');
