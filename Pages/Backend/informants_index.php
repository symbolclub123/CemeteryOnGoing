<?php
include "../../database/connectDatabase.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

$curUser = $_SESSION['user_id'];

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

function getInformants()
{
    global $conn;

    // Construct the SQL query to select all informants except those with fullname 'admin'
    $query = "SELECT * FROM informants WHERE fullname != 'admin' ORDER BY fullname ASC";

    // Execute the query
    $result = mysqli_query($conn, $query);

    $informants = [];

    // Fetch data into an array
    while ($row = mysqli_fetch_assoc($result)) {
        $informants[] = $row;
    }

    return $informants;
}

function updateInformant($informantId, $fullName, $address, $contactNumber, $email)
{
    global $conn;

    // Check if the given name already exists for another informant
    $existingInformantQuery = "SELECT * FROM informants WHERE fullname = '$fullName' AND informant_id != $informantId";
    $existingInformantResult = mysqli_query($conn, $existingInformantQuery);
    
    if (mysqli_num_rows($existingInformantResult) > 0) {
        return false; // Name already exists for another informant
    }

    // Update the informant information
    $query = "UPDATE informants 
              SET fullname = '$fullName', 
                  addresss = '$address', 
                  contactNo = '$contactNumber', 
                  emailadd = '$email'
              WHERE informant_id = $informantId";

    return mysqli_query($conn, $query);
}

function addInformant($IFullName, $Iaddress, $IContact, $Iemail)
{
    global $conn;

    // Normalize to uppercase
    $IFullName = strtoupper($IFullName);

    $existingQuery = "SELECT informant_id FROM informants WHERE fullname = '$IFullName'";
    $existingResult = mysqli_query($conn, $existingQuery);

    if (mysqli_num_rows($existingResult) > 0) {
        // Informant with the same name already exists
        return false;
    }

    $query = "INSERT INTO informants (fullname, addresss, contactNo, emailadd) VALUES ('$IFullName', '$Iaddress', '$IContact', '$Iemail')";
    
    if (mysqli_query($conn, $query)) {
        // Return the informant ID
        return mysqli_insert_id($conn);
    } else {
        // Handle the error
        echo 'Error: ' . mysqli_error($conn);
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['informant_id'])) {
        // Update Informant Information
        $informantId = mysqli_real_escape_string($conn, $_POST['informant_id']);
        $fullName = mysqli_real_escape_string($conn, strtoupper($_POST['full_name']));
        $address = mysqli_real_escape_string($conn, strtoupper($_POST['address']));
        $contactNumber = mysqli_real_escape_string($conn, $_POST['contact_no']);
        $email = mysqli_real_escape_string($conn, strtolower($_POST['email']));

        // Update the informant in the database
        $updateResult = updateInformant($informantId, $fullName, $address, $contactNumber, $email);

        if ($updateResult) {
            $_SESSION["success"] = "Informant Information has been updated";
        } else {
            $_SESSION["error"] = "Unable to Update informant information";
        }
    } else {
        // Add New Informant Information
        $IFullName = mysqli_real_escape_string($conn, strtoupper($_POST['add_full_name']));
        $Iaddress = mysqli_real_escape_string($conn, strtoupper($_POST['add_address']));
        $IContact = mysqli_real_escape_string($conn, strtoupper($_POST['add_contact_no']));
        $Iemail = mysqli_real_escape_string($conn, strtoupper($_POST['add_email']));

        $informantId = addInformant($IFullName, $Iaddress, $IContact, $Iemail);

        if ($informantId !== false) {
            $_SESSION["success"] = "Informant Information has been added";
        } else {
            // Informant with the same name already exists or there was an error
            $_SESSION["error"] = "Unable to add informant information";
        }
    }
}

$userData = getUserId($curUser);
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);
$informants = getInformants();
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Informants</title>
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
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Informants List</h1>
            <div class="bg-white rounded p-5">
            <?php if (isset($_SESSION["error"])) { ?>
                <p style="color: red"><?= $_SESSION["error"]; ?></p>
            <?php unset($_SESSION["error"]); } ?>
            <?php if (isset($_SESSION["success"])) { ?>
                <p style="color: green"><?= $_SESSION["success"]; ?></p>
            <?php unset($_SESSION["success"]); } ?>
                <div class="flex justify-between items-center">
                    <div>
                        <!-- Modal toggle -->
                        <button data-modal-target="add-informant-modal" data-modal-toggle="add-informant-modal"
                            class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mb-3"
                            type="button">
                            Add Informant
                        </button>

                        <!-- Main modal -->
                        <div id="add-informant-modal" tabindex="-1" aria-hidden="true"
                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="relative p-4 w-full max-w-2xl max-h-full">
                                <!-- Modal content -->
                                <div class="relative bg-white rounded-lg shadow">
                                    <!-- Modal header -->
                                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                                        <h3 class="text-xl font-semibold text-gray-900">
                                            Add Informant
                                        </h3>
                                        <button type="button"
                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                            data-modal-hide="add-informant-modal">
                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 14 14">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                            </svg>
                                            <span class="sr-only">Close modal</span>
                                        </button>
                                    </div>
                                    <form action="#" method="post">
                                        <!-- Modal body -->
                                        <div class="p-4 md:p-5 space-y-4">

                                            <div class="mb-3">
                                                <label for="full_name"
                                                    class="block mb-2 text-sm font-medium text-gray-900">
                                                    Full Name
                                                </label>
                                                <input type="text" id="full_name" name="add_full_name"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                    placeholder="" required />
                                                <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500">
                                                    (Last Name, First Name, Middle Name)
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone"
                                                    class="block mb-2 text-sm font-medium text-gray-900">Phone
                                                    number</label>
                                                <input type="text" id="phone" name="add_contact_no"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                    placeholder="09000000000" required />
                                            </div>
                                            <div class="mb-3">
                                                <label for="email"
                                                    class="block mb-2 text-sm font-medium text-gray-900">Email
                                                    address</label>
                                                <input type="email" id="email" name="add_email"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                    placeholder="john.doe@company.com" required />
                                            </div>
                                            <div class="mb-3">
                                                <label for="address"
                                                    class="block mb-2 text-sm font-medium text-gray-900">Address</label>
                                                <input type="text" id="address" name="add_address"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                    placeholder="Write your address here..." required />
                                            </div>
                                        </div>
                                        <!-- Modal footer -->
                                        <div
                                            class="flex justify-end items-center p-4 md:p-5 border-t border-gray-200 rounded-b ">
                                            <button type="submit"
                                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                Submit
                                            </button>
                                            <button data-modal-hide="add-informant-modal" type="button"
                                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                                Close
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!--<div>
                        <button data-dropdown-toggle="blocks-dropdown"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center"
                            type="button">
                            Block
                            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                         Dropdown menu 
                        <div id="blocks-dropdown"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="blocks-dropdown">
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">Block 1</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">Block 2</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">Block 3</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">Block 4</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100">Block 5</a>
                                </li>
                            </ul>
                        </div>

                    </div>-->
                </div>
                <?php if (empty($informants)): ?>
                    <p>No deceased persons found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto"  style="height: 60vh;">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
                        <thead class="text-xs text-gray-700 uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">ID</th>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Address</th>
                                <th scope="col" class="px-6 py-3">Contact #</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($informants as $informant): ?>
                                <tr>
                                    <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($informant['informant_id'])) ?></td>
                                    <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($informant['fullname'])) ?></td>
                                    <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($informant['addresss'])) ?></td>
                                    <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($informant['contactNo'])) ?></td>
                                    <td scope="col" class="px-6 py-3"><?= ucfirst(strtolower($informant['emailadd'])) ?></td>
                                    <td class="px-6 !py-4">
                                        <a href="informant_deceased.php?user_id=<?= $curUser ?>&iId=<?= $informant['informant_id'] ?>"
                                            class="text-xs font-medium text-info hover:text-white border border-info hover:bg-info focus:ring-4 focus:outline-none focus:ring-info font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                            View Deceased Persons
                                        </a>
                                        <button type="button" data-modal-target="edit-informant-<?= $informant['informant_id'] ?>-modal" 
                                            data-modal-toggle="edit-informant-<?= $informant['informant_id'] ?>-modal"
                                            class="text-xs font-medium text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-info font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                            Edit
                                        </button>
                                        <div id="edit-informant-<?= $informant['informant_id'] ?>-modal" tabindex="-1" aria-hidden="true"
                                            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                            <div class="relative p-4 w-full max-w-2xl max-h-full">
                                                <!-- Modal content -->
                                                <div class="relative bg-white rounded-lg shadow">
                                                    <!-- Modal header -->
                                                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                                                        <h3 class="text-xl font-semibold text-gray-900">
                                                            Edit Informant
                                                        </h3>
                                                        <button type="button"
                                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                                            data-modal-hide="edit-informant-<?= $informant['informant_id'] ?>-modal">
                                                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                                            </svg>
                                                            <span class="sr-only">Close modal</span>
                                                        </button>
                                                    </div>
                                                    <form action="#" method="post">
                                                        <!-- Modal body -->
                                                        <div class="p-4 md:p-5 space-y-4">
                                                            <input type="hidden" name="informant_id" value="<?= $informant['informant_id'] ?>">
                                                            <div class="mb-3">
                                                                <label for="full_name_<?= $informant['informant_id'] ?>"
                                                                    class="block mb-2 text-sm font-medium text-gray-900">
                                                                    Full Name
                                                                </label>
                                                                <input type="text" id="full_name_<?= $informant['informant_id'] ?>" name="full_name"
                                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                                    placeholder="" value="<?= $informant['fullname'] ?>" required />
                                                                <p id="helper-text-explanation"
                                                                    class="mt-2 text-sm text-gray-500">
                                                                    (Last Name, First Name, Middle Name)
                                                                </p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="phone_<?= $informant['informant_id'] ?>"
                                                                    class="block mb-2 text-sm font-medium text-gray-900">Phone
                                                                    number</label>
                                                                <input type="text" id="phone_<?= $informant['informant_id'] ?>" name="contact_no"
                                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                                    placeholder="09000000000" value="<?= $informant['contactNo'] ?>" required />
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="email_<?= $informant['informant_id'] ?>"
                                                                    class="block mb-2 text-sm font-medium text-gray-900">Email
                                                                    address</label>
                                                                <input type="email" id="email_<?= $informant['informant_id'] ?>" name="email"
                                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                                    placeholder="john.doe@company.com" value="<?= $informant['emailadd'] ?>" required />
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="address_<?= $informant['informant_id'] ?>"
                                                                    class="block mb-2 text-sm font-medium text-gray-900">Address</label>
                                                                <input type="text" id="email_<?= $informant['informant_id'] ?>" name="address"
                                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                                                    placeholder="john.doe@company.com" value="<?= $informant['addresss'] ?>" required />
                                                            </div>
                                                        </div>
                                                        <!-- Modal footer -->
                                                        <div class="flex justify-end items-center p-4 md:p-5 border-t border-gray-200 rounded-b ">
                                                            <button type="submit"
                                                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                                Save Changes
                                                            </button>
                                                            <button data-modal-hide="edit-informant-<?= $informant['informant_id'] ?>-modal" type="button"
                                                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                                                Close
                                                            </button>
                                                        </div>
                                                    </form>
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
                ordering: true,
            };

            $(".data-table").DataTable(dataTableOptions);
        });
    </script>
</body>

</html>