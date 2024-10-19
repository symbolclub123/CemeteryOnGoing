<?php
include "../../database/connectDatabase.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

$curUser = $_SESSION['user_id'];

echo $curUser;

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

function getDeceasedPersonsByBlock($block) {
    global $conn;

    // Construct the base SQL query
    $query = "SELECT dr.*, 
                    i.fullname AS informant_name, 
                    i.addresss AS informant_address, 
                    i.contactNo AS informant_contact, 
                    i.emailadd AS informant_email
                FROM deceased_records dr
                LEFT JOIN informants i ON dr.informant_id = i.informant_id 
                WHERE dr.status = 'OCCUPIED'"; // 'status' corrected to 'statuss'

    // Add WHERE clause conditionally based on the block parameter
    if ($block !== 'all') {
        $block = mysqli_real_escape_string($conn, $block);
        $query .= " AND dr.block = '$block'";
    }

    $query .= " ORDER BY dr.id DESC";

    // Execute the query
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }

    $deceasedPersons = [];

    // Fetch data into an array
    while ($row = mysqli_fetch_assoc($result)) {
        $deceasedPersons[] = $row; // Changed to $deceasedPersons[]
    }

    return $deceasedPersons;
}


$userData = getUserId($curUser); // this is where you get the user
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Deceased Persons</title>
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

    <link rel="stylesheet" href="../../Assets/datatables/dataTables.tailwindcss.min.css" />
    <link rel="stylesheet" href="../../Assets/jquery/jquery.dataTables.min.css" />
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
        <div class="p-4 rounded-lg mt-14">
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Deceased Persons List</h1>
            <div class="bg-white rounded p-5">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex">
                        
                    </div>
                    <div>

                        <button data-dropdown-toggle="blocks-dropdown"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center"
                            type="button">
                            Block 
                            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <?php
                        
                        if (isset($_GET['block'])) {
                            $block = $_GET['block'];
                        } else {
                            // Default to 'all' when the 'block' parameter is not set
                            $block = 'all';
                        }
                            // Call the function to get deceased persons for the specified block
                        $deceasedPersons = getDeceasedPersonsByBlock($block);
                        
                        ?>
                        <!-- Dropdown menu -->
                        <div id="blocks-dropdown"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                            <ul class="py-2 text-sm text-gray-700"
                                aria-labelledby="blocks-dropdown">
                                <li>
                                    <a href="deceased_persons_index.php?block=all"
                                        class="block px-4 py-2 hover:bg-gray-100">All</a>
                                </li>
                                <?php
                                // Sample blocks array (replace this with your actual function call)
                                $blocks = [1, 2, 3, 4, 5];

                                // Loop through blocks and generate list items
                                foreach ($blocks as $block):
                                ?>
                                    <li>
                                        <a href="deceased_persons_index.php?block=<?= urlencode($block) ?>"
                                            class="block px-4 py-2 hover:bg-gray-100"><?= ucfirst(strtolower($block)) ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                    </div>
                </div>
                <?php if (empty($deceasedPersons)): ?>
                    <p>No deceased persons found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto"  style="height: 55vh;">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
                            <thead class="text-xs text-gray-700 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Last Name</th>
                                    <th scope="col" class="px-6 py-3">Middle Name</th>
                                    <th scope="col" class="px-6 py-3">First Name</th>
                                    <th scope="col" class="px-6 py-3">Sex</th>
                                    <th scope="col" class="px-6 py-3">Date of Death</th>
                                    <th scope="col" class="px-6 py-3">Block #</th>
                                    <th scope="col" class="px-6 py-3">Lot #</th>
                                    <th scope="col" class="px-6 py-3">Informant</th>
                                    <th scope="col" class="px-6 py-3">Relationship</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deceasedPersons as $deceasedPerson): ?>
                                    <tr>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['last_name'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['middle_name'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['first_name'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['deceased_sex'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['date_of_death'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['block'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['lots'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['informant_name'])) ?></td>
                                        <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($deceasedPerson['relation'])) ?></td>
                                        <td scope="col" class="px-6 py-3">
                                            <!-- the number 1 in "view-deceased-1-modal" indicates the id of deceased -->
                                        <button type="button" data-modal-target="view-deceased-<?= htmlspecialchars($deceasedPerson['id']) ?>-modal"
                                            data-modal-toggle="view-deceased-<?= htmlspecialchars($deceasedPerson['id']) ?>-modal"
                                            class="text-xs font-medium text-info hover:text-white border border-info hover:bg-info focus:ring-4 focus:outline-none focus:ring-info font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                            View
                                        </button>

                                        <!-- Modal -->
                                        <div id="view-deceased-<?= htmlspecialchars($deceasedPerson['id']) ?>-modal" tabindex="-1" aria-hidden="true"
                                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                            <div class="relative p-4 w-full max-w-4xl max-h-full">
                                                <!-- Modal content -->
                                                <div class="relative bg-white rounded-lg shadow">
                                                    <!-- Modal header -->
                                                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                                                        <h3 class="text-xl font-semibold text-gray-900">
                                                            View Deceased Person Information
                                                        </h3>
                                                        <button type="button"
                                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                                            data-modal-hide="view-deceased-<?= htmlspecialchars($deceasedPerson['id']) ?>-modal">
                                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 14 14">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                                            </svg>
                                                            <span class="sr-only">Close modal</span>
                                                        </button>
                                                    </div>

                                                    <!-- Modal body -->
                                                    <div class="p-4 md:p-5 space-y-4 text-black">
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div class="border rounded p-3">
                                                                <h1 class="text-xl mb-3 text-black font-bold">
                                                                    Personal Information
                                                                </h1>
                                                                <table>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>Name</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['first_name']))) ?> <?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['middle_name']))) ?> <?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['last_name']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Barangay</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['barangay']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>City/Municipality</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['city_municipality']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Province</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['province']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Sex</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['deceased_sex']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Religion</td>
                                                                            <td><?= htmlspecialchars($deceasedPerson['religion']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Nationality</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['nationality']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Civil Status</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['civil_status']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Occupation</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['occupation']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Relationship to the Informant</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['relation']))) ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="border rounded p-3">
                                                                <div class="mb-2">
                                                                    <h1 class="text-xl mb-3 text-black font-bold">
                                                                        Death Information
                                                                    </h1>
                                                                    <table>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Death Cert Ref</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['death_reference']))) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Date of Death</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['date_of_death']))) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Cause of Death</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['cause_of_death']))) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Place of Death</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['place_of_death']))) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Block No</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['block']))) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Lot No</td>
                                                                                <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['lots']))) ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <h1 class="text-xl mb-3 text-black font-bold">
                                                                    Informant Information
                                                                </h1>
                                                                <table>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>Name</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['informant_name']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Address</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['informant_address']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Contact No</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['informant_contact']))) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Email Address</td>
                                                                            <td><?= ucfirst(strtolower(htmlspecialchars($deceasedPerson['informant_email']))) ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <!-- Modal footer -->
                                                    <div class="flex justify-end items-center p-4 md:p-5 border-t border-gray-200 rounded-b">
                                                        <button data-modal-hide="view-deceased-<?= htmlspecialchars($deceasedPerson['id']) ?>-modal"
                                                            type="button"
                                                            class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            
                                        </td>
                                    </tr>    
                                <?php endforeach; ?>                            
                            </tbody>
                        </table> 
                    </div>    
                <?php endif; ?> 
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
    <script src="../../Assets/datatables/jquery.datatables.min.js"></script>
    <script>
        $(document).ready(function () {
            const dataTableOptions = {
                info: false,
                ordering: false,
            };

            $(".data-table").DataTable(dataTableOptions);
        });
    </script>
</body>

</html>