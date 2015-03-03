<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// define the file dirs
define('MAINDIR', dirname(__FILE__) . '/');
define('INCDIR', MAINDIR . 'inc/');
define('PAGEDIR', MAINDIR . 'pages/');
require INCDIR . 'functions_global.php';

// set the database
require INCDIR . 'class_db_handle.php';
$db = new db_handle();
// join the db party
$DbHost = 'localhost';
$DbUser = 'welinkco_tracker';
$DbPassword = 'oP4NBB^V_0qu';
$DbDatabase = 'welinkco_tracker';
$db->connect($DbHost, $DbUser, $DbPassword, $DbDatabase);

require INCDIR . 'class_cron.php';
$cron = new cron();

$cron->fix_prs_with_id($_GET['exercise_id']);
?>