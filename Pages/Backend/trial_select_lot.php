<?php
include "../../database/connectDatabase.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

$curUser = $_SESSION['user_id'];
$curInformant = isset($_GET['iId']) ? $_GET['iId'] : null;
$block = isset($_GET['block']) ? $_GET['block'] : null;

echo $curUser;
echo $curInformant;
echo $block;

function getUserId($curUser)
{
    global $conn;

    $query = "SELECT * FROM users WHERE user_id = $curUser";

    $result = mysqli_query($conn, $query);

    return mysqli_fetch_assoc($result);
}

function countUsersByStatus($conn, $status) {
    $query = "SELECT COUNT(*) FROM users WHERE status = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);

        mysqli_stmt_close($stmt);

        return $count;
    } else {
        // Log error or handle error appropriately
        error_log("Failed to prepare the statement: " . mysqli_error($conn));
        return false;
    }
}

function getInformant($conn, $informantId) {
    $informant = null;

    $query = "SELECT * FROM informants WHERE informant_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $informantId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $informant = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }

    return $informant;
}

function getLotFromBlock($block)
{
    global $conn;

    $block = intval($block);

    $query = "SELECT * FROM lots WHERE block_id = $block";

    $result = mysqli_query($conn, $query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getMapDetails($block) {
    global $conn;

    $query = "SELECT * FROM maps WHERE block_num_location = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $block);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function countDeceasedByBlockLot($conn) {
    $sql = "SELECT block, lots, COUNT(*) AS total FROM deceased_records GROUP BY block, lots";
    
    if ($result = $conn->query($sql)) {
        $counts = [];
        
        while ($row = $result->fetch_assoc()) {
            $counts[$row['block']][$row['lots']] = $row['total'];
        }
        
        return $counts;
    } else {
        return false;
    }
}


$userData = getUserId($curUser);
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);
$informant = getInformant($conn, $curInformant);
$blockLotData = getLotFromBlock($block);
$mapDetails = getMapDetails($block);
$deceasedCounts = countDeceasedByBlockLot($conn);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Informants Select Block & Lot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#A844B9",
                        primaryDarker: "#863694",
                        secondary: "#F7F7F7",
                        darker: "#191B1E",
                        info: "#17a2b8", // Added info color
                        skyblue: "#87CEEB",
                        mutedYellow: "#EBD4A2",
                    },
                    screens: {
                        tablet: "769px", // Custom tablet breakpoint
                    },
                },
            },
        };
    </script>
    <style>
        .lot {
            display: inline-block; 
            width: 38px; /* Set a fixed width */
            height: 18px; /* Set a fixed height */
            padding: 0px; /* Adjust padding if necessary */
            margin: 1px;
            color: white; 
            text-align: center; 
            text-decoration: none; 
            border-radius: 5px; 
            font-size: 12px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            transition: background-color 0.3s ease;
        }

        .lot:hover {
            background-color: #0056b3;
        }
        /* Ensure the sidebar has a high z-index */
        #logo-sidebar {
            z-index: 50;
        }
        /* Optional: Add a higher z-index to the button to ensure it is clickable */
        [data-drawer-toggle="logo-sidebar"] {
            z-index: 60;
        }

        .disabled-lot {
            pointer-events: none; /* Prevent clicking */
            opacity: 0.5; /* Make the disabled link look faded */
            cursor: not-allowed; /* Change the cursor to indicate it's not clickable */
        }
    </style>


    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
</head>

<body class="bg-gray-100">


<nav class="fixed top-0 z-40 w-full bg-white border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-between rtl:justify-end px-4">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                    aria-controls="logo-sidebar" type="button"
                    class="inline-flex items-center p-2 text-sm text-primary rounded-lg sm:hidden hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center">
                <div class="flex items-center ms-3">
                    <button type="button"
                        class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                        aria-expanded="false" data-dropdown-toggle="dropdown-user">
                        <span class="sr-only">Open user menu</span>
                        <svg class="w-8 h-8 rounded-full text-white" xmlns="http://www.w3.org/2000/svg" fill="#ffffff" viewBox="0 0 512 512">
                            <path d="M399 384.2C376.9 345.8 335.4 320 288 320H224c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8s107.8-24.7 143-63.8zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 16a72 72 0 1 0 0-144 72 72 0 1 0 0 144z"/>
                        </svg>
                    </button>
                    <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow"
                        id="dropdown-user">
                        <div class="px-4 py-3" role="none">
                            <p class="text-sm text-gray-900" role="none">
                                Name: <?= htmlspecialchars($userData['user_name']) ?>
                            </p>
                            <p class="text-sm font-medium text-gray-900 truncate" role="none">
                                Status: <?= htmlspecialchars($userData['status']) ?>
                            </p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="change_password.php?user_id=<?= $curUser?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem">Change Password</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-30 w-64 h-screen pt-20 transition-transform transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0">
    <!-- Add a container to control the layout and spacing of the content inside the sidebar -->
    <div class="relative">
        <img src="../../Assets/images/logo.png" alt="Description" class="relative -top-5 left-20 w-20 h-auto">
    </div>
    <div class="flex justify-end p-4">
        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-primary rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary sm:hidden">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
        </button>
    </div>
    <div class="flex flex-col h-full px-3 pb-4">
        <!-- Center the content vertically and horizontally if needed -->
        <div class="flex flex-col items-center mb-4">
            <a href="#" class="flex items-center">
            <span class="self-center text-md  whitespace-nowrap text-primary text-center">
                Calauan Public Cemetery<br>Information Management System
            </span>
            </a>
        </div>  
        <!-- Sidebar content -->
        
        <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
            <ul class="space-y-2 font-medium">
                <!-- Sidebar menu items -->
                <li>
                    <a href="dashboard.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">

                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="employees_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">

                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Employees</span>
                        <span
                            class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-primary bg-white  group-hover:bg-white  group-hover:text-primary rounded-full">
                            <?= htmlspecialchars($userCount) ?>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="informants_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">

                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="ms-3">Informants</span>
                    </a>
                </li>
                <li>
                    <a href="deceased_persons_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">

                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="ms-3">Deceased Persons</span>
                    </a>
                </li>
                <!-- More items here -->
            </ul>
        </div>
        </div>
        <!-- Sign out button at the bottom -->
        <div class="absolute bottom-0 left-0 w-full p-4">
            <a href="../../Pages/Login/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</a>
        </div>
    </div>
</aside>
    <div class="p-4 sm:ml-64">
        <div class="p-3 rounded-lg mt-14">
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Add Deceased Person - Block <?= $block;?> Select Lot</h1>
            <div class="bg-white rounded p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <a href="trial_select_block.php?user_id=<?= $curUser ?>&iId=<?= $informant['informant_id'] ?>"
                        class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Back to Block Selection
                        </a>
                    </div>
                </div>
                <div class="flex justify-start items-center mt-4">
                    <div class="mr-4">
                        <div style="width: 20px; height: 20px; background-color: #ff0000; display: inline-block; border: 1px solid #000;"></div>
                        <span>Fully Occupied (Red)</span>
                    </div>
                    <div class="mr-4">
                        <div style="width: 20px; height: 20px; background-color: #007bff; display: inline-block; border: 1px solid #000;"></div>
                        <span>One Occupied (Blue)</span>
                    </div>
                    <div class="mr-4">
                        <div style="width: 20px; height: 20px; background-color: #ffffff; display: inline-block; border: 1px solid #000;"></div>
                        <span>Not Occupied (White)</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-row">
                        <div class="relative bg-gray-100 overflow-auto" style="width: 77vw;">
                            <!-- <a href="#" class="lot" style="position: absolute; left: 500px; top: 20px;">178</a> -->

                            <?php
                            // Check if there are any lots
                            if ($blockLotData) {
                                foreach ($blockLotData as $lot) {
                                    // Default background color and text color
                                    $bgColor = "#ffffff"; // White background
                                    $textColor = "#000000"; // Black text
                                    $hrefAttribute = 'href="trial_add_deceased.php?user_id=' . $curUser . '&iId=' . $informant['informant_id'] . '&lot=' . $lot['id'] . '&block=' . $block . '"'; // Default link
                                    $disabledClass = ''; // CSS class for disabled state (empty by default)
                                    
                                    // Check if the block and lot combination exists in the deceased counts
                                    if (isset($deceasedCounts[$block][$lot['lot']])) {
                                        $count = $deceasedCounts[$block][$lot['lot']];
                                        
                                        // Change the background color based on the count
                                        if ($count == 2) {
                                            $bgColor = "#ff0000"; // Red background
                                            $textColor = "#ffffff"; // White text
                                            $hrefAttribute = ''; // Remove href to disable the link
                                            $disabledClass = 'disabled-lot'; // Add a CSS class to indicate disabled
                                        } elseif ($count == 1) {
                                            $bgColor = "#007bff"; // Blue background
                                            $textColor = "#ffffff"; // White text
                                        }
                                    }
                                    
                                    // Output the link with the dynamic background and text color
                                    echo '<a class="lot ' . $disabledClass . '" ' . $hrefAttribute . ' 
                                    style="background-color: ' . $bgColor . '; color: ' . $textColor . '; position: absolute; left: ' . $lot['left_position'] . 'px; top: ' . $lot['top_position'] . 'px;">
                                    ' . $lot['lot'] . '</a>';
                                }
                            } else {
                                echo "No lots found for this block.";
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <img src="<?= htmlspecialchars($mapDetails['URL']) ?>" alt="map" class="w-full rounded shadow">
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const drawerToggle = document.querySelector('[data-drawer-toggle="logo-sidebar"]');
            const sidebar = document.getElementById('logo-sidebar');

            drawerToggle.addEventListener('click', function () {
                sidebar.classList.toggle('-translate-x-full');
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const openButton = document.querySelector('[data-drawer-toggle]');
            const sidebar = document.getElementById(openButton.getAttribute('data-drawer-target'));

            if (openButton && sidebar) {
                openButton.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <!--<script src="../../Assets/jquery/jquery.min.js"></script>
    <script src="../../Assets/datatables/jquery.datatables.min.js"></script>
    <script>
        $(document).ready(function () {
            const dataTableOptions = {
                info: false,
                ordering: true,
            };

            $(".data-table").DataTable(dataTableOptions);
        });
    </script>-->

</body>

</html>