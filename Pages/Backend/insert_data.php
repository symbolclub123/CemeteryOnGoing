<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include "../../database/connectDatabase.php";

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the block ID
$blockId = 5; // Set the block ID as needed
$totalLots = 511; // Total number of lots to insert

// Check if block_id exists in blocks table
$blockQuery = "SELECT id FROM blocks WHERE id = $blockId";
$blockResult = $conn->query($blockQuery);

if ($blockResult->num_rows == 0) {
    die("Error: Block ID $blockId does not exist in the blocks table.");
}

// Initialize the SQL query
$query = "INSERT INTO lots (block_id, lot) VALUES ";

// Generate values for lots 1 to 437
$values = [];
for ($i = 1; $i <= $totalLots; $i++) {
    $values[] = "($blockId, $i)";
}

// Combine all values into a single query
$query .= implode(", ", $values);

// Print the SQL query for debugging
echo "Executing query: $query<br>";

// Execute the query
if ($conn->query($query) === TRUE) {
    echo "Records created successfully";
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();
