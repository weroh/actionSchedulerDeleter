<?php
set_time_limit(0);

// Start the timer
$start = microtime(true);

// Database configuration
define( 'DB_NAME', '...' );
define( 'DB_USER', '...' );
define( 'DB_PASSWORD', '...' );
define( 'DB_HOST', '...' );

// Connect to the database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: ' . $mysqli->connect_error);
}

// Disable timeouts
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 0);
$mysqli->options(MYSQLI_OPT_READ_TIMEOUT, 0);

// Execute the delete statement
$attemptNumber = rand(1000, 100000);
$query = "DELETE FROM wp_actionscheduler_actions WHERE hook = 'woocommerce_paypal_payments_payment_tokens_migration' LIMIT " . $attemptNumber;
$mysqli->query($query);

// Count the number of affected rows
$deletedRecords = $mysqli->affected_rows;

// Fetch the estimated number of remaining records
/*
$result = $mysqli->query("SELECT COUNT(*) FROM wp_actionscheduler_actions");
$row = $result->fetch_row();
$remainingRecords = $row[0];
*/
$result = $mysqli->query("SHOW TABLE STATUS LIKE 'wp_actionscheduler_actions'");
$row = $result->fetch_assoc();
$remainingRecords = $row['Rows'];

// Close the database connection
$mysqli->close();

// Stop the timer
$end = microtime(true);
$executionTime = round($end - $start, 2);

// Calculate the estimated time for completion
if ($deletedRecords > 0) {
    $remainingTime = $remainingRecords * ($executionTime / $deletedRecords);
}
else {
    $remainingTime = $remainingRecords * ($executionTime / $attemptNumber);
}

// Return the JSON response
header("content-type: application/json");
echo json_encode([
    'attemptNumber' => $attemptNumber,
    'deletedRecords' => $deletedRecords, 
    'remainingRecords' => $remainingRecords,
    'executionTime' => $executionTime,
    'remainingTime' => $remainingTime
]);
