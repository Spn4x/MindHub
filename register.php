<?php

session_start();
require_once 'config/db.php';

$error = '';
$success = '';


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id_number = trim($_POST['id_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $program = trim($_POST['department_or_program']);
    $contact = trim($_POST['contact_number']);
    $password = trim($_POST['password']);
    $confirm_pw = trim($_POST['confirm_password']);

    if (empty($id_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address."'
    } elseif ($password !== $confirm_pw) {
     $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
     $error = "Password must be atleast 6 chars long"
     } else {
    $checkStmt = $pdo->prepare("SELECT user_id FROM users where id_number = ? or email =?");
    $checkStmt->execute([$id_number, $email]);

    if($checkStmt->fetch()){
    $error= "That Student ID Number or Email is already registered.";
}else{

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "insert into users(
    id_number, first_name, last_name, email, password_hash, role, department_or_program, contact_number
    )
    values(?,?,?,?,?,'student',?,?)";

    $insertStmt = $pdo->prepare($sql);

    $success_status = $insertStmt->execute([
    $id_number,
    $first_name,
    $last_name,
    $email,
    $hashed_password,
    $program,
    $contact
    ]);

    if($success_status){
    $success = "Registration successful! You may now proceed to log in.";
}else{
    $error = "Something went wrong while saving your account. Please try again.";
    }
  }
}

