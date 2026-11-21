<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
if (current_user($pdo)) redirect('dashboard.php');
redirect('login.php');
