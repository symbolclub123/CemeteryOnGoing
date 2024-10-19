<?php
include "../../database/connectDatabase.php";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$year = date('y'); // Get current year in YY format
$month = date('m'); // Get current month in MM format
$yearMonth = $year . '-' . $month; // Format like '24-08'

// Check the latest sequence number for the current year and month
$query = "SELECT COALESCE(MAX(sequence), 0) AS last_sequence FROM records WHERE year = '$year' AND month = '$month'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $newSequence = $row['last_sequence'] + 1;

    // Format the custom primary key
    $customPrimaryKey = $yearMonth . '-' . str_pad($newSequence, 3, '0', STR_PAD_LEFT);

    // Sample data
    $name = "John Does";
    $dateOfBirth = "1990-05-15"; // Example date of birth

    // Escape the sample data to prevent SQL injection
    $escapedName = mysqli_real_escape_string($conn, $name);
    $escapedDateOfBirth = mysqli_real_escape_string($conn, $dateOfBirth);

    // Now insert your data with the custom primary key
    $insertDataQuery = "INSERT INTO records (id, year, month, sequence, name, date_of_birth) VALUES ('$customPrimaryKey', '$year', '$month', $newSequence, '$escapedName', '$escapedDateOfBirth')";
    if (mysqli_query($conn, $insertDataQuery)) {
        echo "Record inserted successfully with ID: $customPrimaryKey";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Query error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
