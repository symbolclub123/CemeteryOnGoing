<?php
include "../../database/connectDatabase.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

function getUserId($conn, $curUser)
{
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $curUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function countUsersByStatus($conn, $status) {
    $query = "SELECT COUNT(*) AS count FROM users WHERE status = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

function getInformants($conn)
{
    $query = "SELECT * FROM informants ORDER BY informant_id DESC";
    $result = mysqli_query($conn, $query);
    $informants = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $informants[] = $row;
    }
    return $informants;
}

function getLotCountsByBlock($conn) {
    $sql = "SELECT block_id, COUNT(*) AS lot_count FROM lots GROUP BY block_id";
    
    try {
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Multiply lot count by 2
        foreach ($data as &$row) {
            $row['lot_count'] *= 2;
        }
        
        return $data;
    } catch (Exception $e) {
        // Handle SQL error
        echo "Error: " . $e->getMessage();
        return [];
    }
}

function getOccupiedCounts($conn) {
    $sql = "SELECT block, COUNT(*) AS occupied_count 
            FROM deceased_records 
            WHERE status = 'OCCUPIED' 
            GROUP BY block
            ORDER BY block ASC";
    
    try {
        $result = $conn->query($sql);
        $occupiedCounts = [];
        
        while ($row = $result->fetch_assoc()) {
            $occupiedCounts[$row['block']] = $row['occupied_count'];
        }
        
        return $occupiedCounts;
    } catch (Exception $e) {
        // Handle SQL error
        echo "Error: " . $e->getMessage();
        return [];
    }
}

function getArchivedCounts($conn) {
    $sql = "SELECT block, COUNT(*) AS archived_count 
            FROM deceased_records 
            WHERE status = 'ARCHIVED' 
            GROUP BY block
            ORDER BY block ASC";
    
    try {
        $result = $conn->query($sql);
        $archivedCounts = [];
        
        while ($row = $result->fetch_assoc()) {
            $archivedCounts[$row['block']] = $row['archived_count'];
        }
        
        return $archivedCounts;
    } catch (Exception $e) {
        // Handle SQL error
        echo "Error: " . $e->getMessage();
        return [];
    }
}


// Ensure $conn is available in the functions
$curUser = $_SESSION['user_id'];
$userData = getUserId($conn, $curUser);
$informants = getInformants($conn);
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);

$lotCounts = getLotCountsByBlock($conn);
$occupiedCounts = getOccupiedCounts($conn);
$archivedCounts = getArchivedCounts($conn);

$blockCounts = [];
foreach ($lotCounts as $block) {
    $blockId = $block['block_id'];
    $lotCount = $block['lot_count'];

    // Fetch the occupied count for this specific block
    $occupiedCountForBlock = isset($occupiedCounts[$blockId]) ? $occupiedCounts[$blockId] : 0;

    // Calculate the remaining lots for this block
    $remainingLots = $lotCount - $occupiedCountForBlock;

    // Collect data for each block
    $blockCounts[] = [
        'block_id' => $blockId,
        'unoccupied_count' => $remainingLots,
        'archived_count' => isset($archivedCounts[$blockId]) ? $archivedCounts[$blockId] : 0,
        'occupied_count' => $occupiedCountForBlock
    ];
}

?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Dashboard</title>
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
                    },
                    screens: {
                        tablet: "769px", // Custom tablet breakpoint
                    },
                },
            },
        };
    </script>
    <style>
        /* Ensure the sidebar has a high z-index */
        #logo-sidebar {
            z-index: 50;
        }
        /* Optional: Add a higher z-index to the button to ensure it is clickable */
        [data-drawer-toggle="logo-sidebar"] {
            z-index: 60;
        }
    </style>

    <!-- <link rel="stylesheet" href="../Assets/datatables/dataTables.tailwindcss.min.css" />
  <link rel="stylesheet" href="../Assets/jquery/jquery.dataTables.min.css" /> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="p-4 rounded-lg mt-14">
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Dashboard</h1>
            <!--<div class="grid grid-cols-3 gap-4 mb-4">
                <div
                    class="flex items-center justify-center flex-col p-5 rounded bg-white border border-gray-200 rounded-lg shadow">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-primary">Total Informants</h5>
                    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900">30</h5>
                </div>
                <div
                    class="flex items-center justify-center flex-col p-5 rounded bg-white border border-gray-200 rounded-lg shadow">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-primary">Total Deceased Person</h5>
                    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900">30</h5>
                </div>


            </div>-->
            <!-- <?php
                foreach ($occupiedCounts as $block => $count) {
                    echo "Block $block: Occupied Count = $count<br>";
                }
                foreach ($archivedCounts as $block => $count) {
                    echo "Block $block: Archived Count = $count<br>";
                }
                foreach ($lotCounts as $block) {
                    echo "Block ID: " . htmlspecialchars($block['block_id']) . " - Lot Count (x2): " . htmlspecialchars($block['lot_count']) . "<br>";
                }
            ?> -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div class="col-span-1 md:col-span-2 bg-white rounded p-5">
        <canvas id="barChart" class="w-full"></canvas>
    </div>
    <div class="col-span-1 md:col-start-3">

        <div class="w-full h-full max-w-md p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h5 class="text-xl font-bold leading-none text-primary">Latest Informants</h5>
                <a href="informants_index.php?user_id=<?= $curUser?>" class="text-sm font-medium text-blue-600 hover:underline">
                    View all
                </a>
            </div>
            <div class="flow-root">
                <?php if (empty($informants)): ?>
                    <p>No Informants found.</p>
                <?php else: ?>
                    <div class="flex justify-center items-center overflow-x-auto"  style="height: 55vh;">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
                            <thead class="text-xs text-gray-700 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3">NAME</th>
                                    <!--<th scope="col" class="px-6 py-3">ACTION</th>-->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($informants as $informant): ?>
                                    <tr>
                                        <td scope="col" class="px-6 py-3"><?= ucwords(strtolower($informant['fullname'])) ?></td>
                                        <!--<td scope="col" class="px-6 py-3"><a href="#" class="text-sm font-medium text-blue-600 hover:underline">View</a></td>-->
                                    </tr>    
                                <?php endforeach; ?>                            
                            </tbody>
                        </table>
                    </div>   
                <?php endif; ?> 
            </div>
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
    <script src="../../Assets/jquery/jquery.min.js"></script>
    <!-- <script src="../Assets/datatables/jquery.datatables.min.js"></script> -->
    <script>
$(document).ready(function () {
    var element = document.getElementById("barChart").getContext("2d");

    // PHP arrays encoded as JSON for use in JavaScript
    const blockCounts = <?php echo json_encode($blockCounts); ?>;

    // Extract labels and data
    const labels = blockCounts.map(block => block.block_id);
    const unoccupiedData = blockCounts.map(block => block.unoccupied_count);
    const archivedData = blockCounts.map(block => block.archived_count);
    const occupiedData = blockCounts.map(block => block.occupied_count);

    const data = {
        labels: labels,
        datasets: [
            {
                label: "Unoccupied",
                data: unoccupiedData,
                backgroundColor: "rgba(176, 196, 222, 1)",
                borderWidth: 1,
            },
            {
                label: "Archived",
                data: archivedData,
                backgroundColor: "rgba(169, 169, 169, 1)",
                borderWidth: 1,
            },
            {
                label: "Occupied",
                data: occupiedData,
                backgroundColor: "rgba(85, 107, 47, 1)",
                borderWidth: 1,
            },
        ],
        responsive: true, // Make the chart responsive
        maintainAspectRatio: false, // Don't maintain aspect ratio
        width: '100%', // Set width of the chart
        height: '100%' // Set height of the chart
    };

    var myChart = new Chart(element, {
        type: "bar",
        data: data,
    });
});
</script>

</body>

</html>