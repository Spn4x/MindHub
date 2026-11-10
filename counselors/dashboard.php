<?php
session_start();

if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor','admin'])) {
    header('Location: ../index.php');
    exit;
    } 

    require_once __DIR__ .'/../config/db.php';
    $counselor_id = $_SESSION['user_id'];
    ?>

    $pendingQuery = $pdo->prepare("
    select a.appointment_id, a.appointment_id, a.start_time, a.end_time, a.nature_of_concern, a.consultation_mode, a.student_notes")