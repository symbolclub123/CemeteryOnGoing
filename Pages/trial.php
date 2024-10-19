<?php
// Example values for the coordinates (replace these with actual values fetched from your database)
$backgroundImageUrl = "../Assets/images/Block2.jpg"; // Replace with your actual map image URL

// Create an array to hold the coordinates
$coordinates = [];
$initialTop = 3; // Starting top value in rem
$leftValue = 68; // Fixed left value in rem

// Generate 28 coordinates with descending top values
for ($i = 0; $i < 28; $i++) {
    $coordinates[] = [
        'top' => $initialTop . 'rem',
        'left' => $leftValue . 'rem'
    ];
    $initialTop += 1.4; // Increment the top value for the next button
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Street Map Button Placement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .map-container {
            background-image: url('<?php echo $backgroundImageUrl; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            width: 80%;
            height: 80vh; /* Adjust height as needed */
        }
        .map-button {
            position: absolute;
            width: 2rem; /* Set the width of the buttons */
            height: 1.3rem; /* Set the height of the buttons */
        }
    </style>
</head>
<body>
    <div class="map-container">
        <?php foreach ($coordinates as $index => $coord): ?>
            <a href="#" class="map-button bg-blue-500 text-white rounded text-xs flex items-center justify-center" style="top: <?php echo $coord['top']; ?>; left: <?php echo $coord['left']; ?>;">
                <?php echo $index + 1; ?>
            </a>
        <?php endforeach; ?>
    </div>
</body>
</html>
