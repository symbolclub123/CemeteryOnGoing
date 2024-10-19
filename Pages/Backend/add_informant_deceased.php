<?php
include "../../database/connectDatabase.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

$curUser = $_SESSION['user_id'];
$curInformant = isset($_GET['iId']) ? $_GET['iId'] : null;
$selectBlock = isset($_GET['blockNo']) ? trim($_GET['blockNo']) : null;
$selectLot = isset($_GET['lotNo']) ? trim($_GET['lotNo']) : null;

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

function getSlotBlockLotStatus($conn, $block, $lot) {
    $query = "SELECT *
              FROM deceased_persons
              WHERE statuss = 'UNOCCUPIED' AND blockNo = '$block' AND lotNo = '$lot'
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result); // Fetch the row as an associative array

        if ($row) {
            mysqli_free_result($result); // Free the result set
            return $row; // Return the fetched row
        } else {
            mysqli_free_result($result); // Free the result set
            return null; // Return null if no data found
        }
    } else {
        // Handle query error
        error_log("Failed to fetch data: " . mysqli_error($conn));
        return null; // Return null on query error
    }
}

function isDeceasedPersonExists($lastName, $firstName, $middleName)
{
    global $conn;

    // Escape input to prevent SQL injection
    $lastName = mysqli_real_escape_string($conn, strtoupper($lastName)); //
    $firstName = mysqli_real_escape_string($conn, strtoupper($firstName)); //
    $middleName = mysqli_real_escape_string($conn, strtoupper($middleName)); // 


    // Query to check if the combination already exists for other deceased persons
    $query = "SELECT COUNT(*) AS count FROM deceased_persons 
              WHERE last_name = '$lastName' 
              AND first_name = '$firstName' 
              AND middle_name = '$middleName'";

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['count'] == 0; // If count is 0, the combination is unique
}

function addDeceasedPerson($deceasedPersonId, $lastName, $firstName, $middleName, $barangay, $cityMunicipality, $province, $deceasedSex, $religion, $nationality, $civilStatus, $occupation, $dateOfDeath, $causeOfDeath, $placeOfDeath, $informantId, $relation)
{
    global $conn;

    // Check if the new combination of last name, first name, and middle name is unique
    if (!isDeceasedPersonExists($lastName, $firstName, $middleName, $barangay, $cityMunicipality, $province, $deceasedSex, $religion, $nationality, $civilStatus, $occupation, $dateOfDeath, $causeOfDeath, $placeOfDeath, $relation)) {
        return false; // Not unique, return false or handle accordingly
    }

    $query = "UPDATE deceased_persons 
              SET last_name = '$lastName', 
                  first_name = '$firstName', 
                  middle_name = '$middleName', 
                  barangay = '$barangay', 
                  city_municipality = '$cityMunicipality', 
                  province = '$province', 
                  sex = '$deceasedSex', 
                  religion = '$religion', 
                  nationality = '$nationality', 
                  civilStatus = '$civilStatus', 
                  occupation = '$occupation', 
                  dateOfdeath = '$dateOfDeath', 
                  causeOfdeath = '$causeOfDeath', 
                  placeOfdeath = '$placeOfDeath', 
                  informant_id = '$informantId', 
                  ROTD = '$relation',
                  statuss = 'OCCUPIED'
              WHERE deceased_person_id = $deceasedPersonId";

    return mysqli_query($conn, $query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission

    // Deceased Persons Information
    $deceasedPersonId = mysqli_real_escape_string($conn, strtoupper($_POST['deceased_id']));
    $informantId = mysqli_real_escape_string($conn, strtoupper($_POST['informant_id']));
    $lastName = mysqli_real_escape_string($conn, strtoupper($_POST['last_name'])); //
    $firstName = mysqli_real_escape_string($conn, strtoupper($_POST['first_name'])); //
    $middleName = mysqli_real_escape_string($conn, strtoupper($_POST['middle_name'])); //
    $barangay = mysqli_real_escape_string($conn, strtoupper($_POST['barangay'])); //
    $cityMunicipality = mysqli_real_escape_string($conn, strtoupper($_POST['municipality'])); //
    $province = mysqli_real_escape_string($conn, strtoupper($_POST['province'])); //
    $deceasedSex = mysqli_real_escape_string($conn, strtoupper($_POST['sex'])); //
    $religion = mysqli_real_escape_string($conn, strtoupper($_POST['religion'])); //
    $nationality = mysqli_real_escape_string($conn, strtoupper($_POST['nationality']));  //
    $civilStatus = mysqli_real_escape_string($conn, strtoupper($_POST['civil_status'])); //
    $occupation = mysqli_real_escape_string($conn, strtoupper($_POST['occupation'])); //
    $dateOfDeath = mysqli_real_escape_string($conn, strtoupper($_POST['date_of_death'])); //
    $causeOfDeath = mysqli_real_escape_string($conn, strtoupper($_POST['cause_of_death'])); //
    $placeOfDeath = mysqli_real_escape_string($conn, strtoupper($_POST['place_of_death'])); //
    $relation = mysqli_real_escape_string($conn, strtoupper($_POST['relationship'])); //


    // Update the deceased person in the database
    $updateResult = addDeceasedPerson($deceasedPersonId, $lastName, $firstName, $middleName, $barangay, $cityMunicipality, $province, $deceasedSex, $religion, $nationality, $civilStatus, $occupation, $dateOfDeath, $causeOfDeath, $placeOfDeath, $informantId, $relation);

    if ($updateResult) {
        $_SESSION["success"] = "Deceased Information has been Added";
        header("Location: informant_deceased.php?iId={$informantId}");
        exit();
    } else {
        $_SESSION["error"] = "Unable to Add deceased person information";
    }
}

$userData = getUserId($curUser);
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);
$informant = getInformant($conn, $curInformant);
$slot = getSlotBlockLotStatus($conn, $selectBlock, $selectLot);
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Informants Add Deceased</title>
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
                            <li>
                                <a href="../../Pages/Login/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem">Sign out</a>
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
</aside>

    <div class="p-4 sm:ml-64">
        <div class="p-4 rounded-lg mt-14">
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Add Deceased Person to (Block & lot number)</h1>
            <div class="bg-white rounded p-5">
            <?php if (isset($_SESSION["error"])) { ?>
                <p style="color: red"><?= $_SESSION["error"]; ?></p>
            <?php unset($_SESSION["error"]); } ?>
            <?php if (isset($_SESSION["success"])) { ?>
                <p style="color: green"><?= $_SESSION["success"]; ?></p>
            <?php unset($_SESSION["success"]); } ?>
                <div class="flex justify-between items-center mb-3">
                    <div>

                    </div>
                    <div>

                    </div>
                </div>
                <form action="#" method="post">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border rounded p-3">
                            <h1 class=" text-xl mb-3 text-black font-bold">Personal
                                Information</h1>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div class="mb-3">
                                    <input type="hidden" name="deceased_id" value="<?= $slot['deceased_person_id'] ?>">
                                    <input type="hidden" name="informant_id" value="<?= $informant['informant_id'] ?>">

                                    <label for="first_name" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        First Name
                                    </label>
                                    <input type="text" id="first_name" name="first_name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>
                                <div class="mb-3">
                                    <label for="middle_name" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Middle Name
                                    </label>
                                    <input type="text" id="middle_name" 
                                        name="middle_name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                                <div class="mb-3">
                                    <label for="last_name" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Last Name
                                    </label>
                                    <input type="text" id="last_name" 
                                        name="last_name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div>
                                    <label for="barangay" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Barangay
                                    </label>
                                    <input type="text" id="barangay" 
                                        name="barangay"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                                <div>
                                    <label for="municipality" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        City/Municipality
                                    </label>
                                    <input type="text" id="municipality" 
                                        name="municipality"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>

                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div>
                                    <label for="province" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        province
                                    </label>
                                    <input type="text" id="province" 
                                        name="province"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>
                                <div>
                                    <label for="nationality" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Nationality
                                    </label>
                                    <input type="text" id="nationality" 
                                        name="nationality"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                                <div>
                                    <label for="civil_status" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Civil Status
                                    </label>
                                    <input type="text" id="civil_status" 
                                        name="civil_status"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>

                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div>
                                    <label for="occupation" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Occupation
                                    </label>
                                    <input type="text" id="occupation" 
                                        name="occupation"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                                <div>
                                <label for="sex" class="block mb-2 text-sm font-medium text-gray-900">Sex</label>
                                <select id="sex" name="sex" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                </div>
                                <div>
                                    <label for="religion" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Religion
                                    </label>
                                    <input type="text" id="religion" 
                                        name="religion"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>

                            </div>
                        </div>
                        <div class="flex flex-col border rounded p-3">
                            <h1 class="text-xl mb-3 text-black font-bold">Death
                                Information</h1>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div>
                                    <label for="relationship" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Informant Relation
                                    </label>
                                    <input type="text" id="relationship" 
                                        name="relationship"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                                    
                                </div>

                                <div>
                                    <label for="date_of_death" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Date of Death
                                        (D-M-Y)
                                    </label>
                                    <input type="date" id="date_of_death" 
                                        name="date_of_death"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                                <div>
                                    <label for="cause_of_death" 
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Cause of Death
                                    </label>
                                    <input type="text" id="cause_of_death" 
                                        name="cause_of_death"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />

                                </div>
                            </div>
                            <div>
                                <label for="place_of_death" class="block mb-2 text-sm font-medium text-gray-900">
                                    Place of Death
                                </label>
                                <input type="text" id="place_of_death" 
                                        name="place_of_death"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Submit
                        </button>
                        <a href="select_block.php?user_id=<?= $curUser?>&iId=<?= $informant['informant_id']?>"
                            class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ms-2">
                            Back to Selection
                        </a>

                    </div>
                </form>
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

</body>

</html>