<?php
include "../../database/connectDatabase.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Pages/Login/login.php");
    exit;
}

$curUser = $_SESSION['user_id'];
$curInformant = isset($_GET['iId']) ? $_GET['iId'] : null;

echo $curUser;
echo $curInformant;

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


function getOccupiedCount($conn, $block) {
    if ($block == 'all') {
        $query = "SELECT blockNo, lotNo, COUNT(CASE WHEN statuss = 'OCCUPIED' THEN 1 END) AS occupiedCount
                  FROM deceased_persons
                  GROUP BY blockNo, lotNo
                  ORDER BY blockNo ASC, lotNo ASC";
    } else {
        $query = "SELECT blockNo, lotNo, COUNT(CASE WHEN statuss = 'OCCUPIED' THEN 1 END) AS occupiedCount
                  FROM deceased_persons
                  WHERE blockNo = ?
                  GROUP BY blockNo, lotNo
                  ORDER BY blockNo ASC, lotNo ASC";
    }

    $stmt = mysqli_prepare($conn, $query);
    if ($block != 'all') {
        mysqli_stmt_bind_param($stmt, "s", $block);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $occupiedCounts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $occupiedCounts[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $occupiedCounts;
}

function getMapDetails($block) {
    global $conn;

    $query = "SELECT * FROM maps WHERE location = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $block);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

$userData = getUserId($curUser);
$status = 'employee';
$userCount = countUsersByStatus($conn, $status);
$informant = getInformant($conn, $curInformant);
$selectedBlock = isset($_GET['block']) ? $_GET['block'] : 'all';
$occupiedCounts = getOccupiedCount($conn, $selectedBlock);
$mapDetails = getMapDetails($selectedBlock);
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


    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
</head>

<body class="bg-gray-100">


    <nav class="fixed top-0 z-40 w-full bg-white border-b border-gray-200">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                        aria-controls="logo-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-primary rounded-lg sm:hidden hover:bg-white focus:outline-primaryus:ring-2 focus:ring-primary hover:text-white dark:focus:ring-gray-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                            </path>
                        </svg>
                    </button>
                    <a href="#" class="flex ms-2 md:me-24">
                        <!-- <img src="" class="h-8 me-3" alt="FlowBite Logo" /> -->
                        <span
                            class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap text-primary">Calauan
                            Public Cemetery - Information Management System</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center ms-3">
                        <div>
                            <button type="button"
                                class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                                aria-expanded="false" data-dropdown-toggle="dropdown-user">
                                <span class="sr-only">Open user menu</span>
                                <svg class="w-8 h-8 rounded-full text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="#ffffff" viewBox="0 0 512 512">

                                    <path
                                        d="M399 384.2C376.9 345.8 335.4 320 288 320H224c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8s107.8-24.7 143-63.8zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 16a72 72 0 1 0 0-144 72 72 0 1 0 0 144z" />
                                </svg>
                            </button>
                        </div>
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
                                    <a href="change_password.php?user_id=<?= $curUser?>"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        role="menuitem">Profile</a>
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

    <aside id="logo-sidebar"
        class="fixed top-0 left-0 z-30 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0"
        aria-label="Sidebar">
        <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
            <ul class="space-y-2 font-medium">
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
                            viewBox="0 0 640 512">

                            <path
                                d="M211.2 96a64 64 0 1 0 -128 0 64 64 0 1 0 128 0zM32 256c0 17.7 14.3 32 32 32h85.6c10.1-39.4 38.6-71.5 75.8-86.6c-9.7-6-21.2-9.4-33.4-9.4H96c-35.3 0-64 28.7-64 64zm461.6 32H576c17.7 0 32-14.3 32-32c0-35.3-28.7-64-64-64H448c-11.7 0-22.7 3.1-32.1 8.6c38.1 14.8 67.4 47.3 77.7 87.4zM391.2 226.4c-6.9-1.6-14.2-2.4-21.6-2.4h-96c-8.5 0-16.7 1.1-24.5 3.1c-30.8 8.1-55.6 31.1-66.1 60.9c-3.5 10-5.5 20.8-5.5 32c0 17.7 14.3 32 32 32h224c17.7 0 32-14.3 32-32c0-11.2-1.9-22-5.5-32c-10.8-30.7-36.8-54.2-68.9-61.6zM563.2 96a64 64 0 1 0 -128 0 64 64 0 1 0 128 0zM321.6 192a80 80 0 1 0 0-160 80 80 0 1 0 0 160zM32 416c-17.7 0-32 14.3-32 32s14.3 32 32 32H608c17.7 0 32-14.3 32-32s-14.3-32-32-32H32z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Employees</span>
                        <span
                            class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-white bg-primary  group-hover:bg-white  group-hover:text-primary rounded-full">
                            <?= htmlspecialchars($userCount) ?>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="slot_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">
                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 576 512">

                            <path
                                d="M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z" />

                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Slots</span>
                        <!-- <span
                            class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-white bg-primary  group-hover:bg-white  group-hover:text-primary rounded-full">
                            10
                        </span> -->
                    </a>
                </li>
                <li>
                    <a href="informants_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-white bg-primary rounded-lg hover:bg-primary hover:text-white group">
                        <svg class="w-5 h-5 text-white bg-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 640 512">

                            <path
                                d="M72 88a56 56 0 1 1 112 0A56 56 0 1 1 72 88zM64 245.7C54 256.9 48 271.8 48 288s6 31.1 16 42.3V245.7zm144.4-49.3C178.7 222.7 160 261.2 160 304c0 34.3 12 65.8 32 90.5V416c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V389.2C26.2 371.2 0 332.7 0 288c0-61.9 50.1-112 112-112h32c24 0 46.2 7.5 64.4 20.3zM448 416V394.5c20-24.7 32-56.2 32-90.5c0-42.8-18.7-81.3-48.4-107.7C449.8 183.5 472 176 496 176h32c61.9 0 112 50.1 112 112c0 44.7-26.2 83.2-64 101.2V416c0 17.7-14.3 32-32 32H480c-17.7 0-32-14.3-32-32zm8-328a56 56 0 1 1 112 0A56 56 0 1 1 456 88zM576 245.7v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM320 32a64 64 0 1 1 0 128 64 64 0 1 1 0-128zM240 304c0 16.2 6 31 16 42.3V261.7c-10 11.3-16 26.1-16 42.3zm144-42.3v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM448 304c0 44.7-26.2 83.2-64 101.2V448c0 17.7-14.3 32-32 32H288c-17.7 0-32-14.3-32-32V405.2c-37.8-18-64-56.5-64-101.2c0-61.9 50.1-112 112-112h32c61.9 0 112 50.1 112 112z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Informants</span>
                        <!-- <span
                            class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-white bg-primary  group-hover:bg-white  group-hover:text-primary rounded-full">
                            10
                        </span> -->
                    </a>
                </li>
                <li>
                    <a href="deceased_persons_index.php?user_id=<?= $curUser?>"
                        class="flex items-center p-2 text-primary rounded-lg hover:bg-primary hover:text-white group">
                        <svg class="flex-shrink-0 w-5 h-5 text-primary transition duration-75 group-hover:text-white "
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 640 512">

                            <path
                                d="M0 224.2C0 100.6 100.2 0 224 0h24c95.2 0 181.2 69.3 197.3 160.2c2.3 13 6.8 25.7 15.1 36l42 52.6c6.2 7.8 9.6 17.4 9.6 27.4c0 24.2-19.6 43.8-43.8 43.8H448v0 32L339.2 365.6c-11 1.4-19.2 10.7-19.2 21.8c0 11.6 9 21.2 20.6 21.9L448 416v16c0 26.5-21.5 48-48 48H320v8c0 13.3-10.7 24-24 24H256v0H96c-17.7 0-32-14.3-32-32V407.3c0-16.7-6.9-32.5-17.1-45.8C16.6 322.4 0 274.1 0 224.2zm352-.2a32 32 0 1 0 0-64 32 32 0 1 0 0 64zM464 384a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm152-24a24 24 0 1 1 0 48 24 24 0 1 1 0-48zM592 480a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM552 312a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm40-24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM552 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Deceased Persons</span>
                        <!-- <span
                            class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-white bg-primary  group-hover:bg-white  group-hover:text-primary rounded-full">
                            10
                        </span> -->
                    </a>
                </li>

            </ul>
        </div>
    </aside>

    <div class="p-4 sm:ml-64">
        <div class="p-3 rounded-lg mt-14">
            <h1 class="text-3xl text-primary font-semibold mb-4 ">Add Deceased Person - Select Block and Lot</h1>
            <div class="bg-white rounded p-5">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <!-- Show Entries Dropdown -->
                        <label for="entries" class="mr-2 text-gray-700">Show</label>
                        <select id="entries" class="border border-gray-300 rounded-lg text-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="5">4</option>
                            <option value="10">8</option>
                        </select>
                        <label for="entries" class="ml-2 text-gray-700">entries</label>
                    </div>
                    <div>
                        <a href="informant_deceased.php?user_id=<?= $curUser ?>&iId=<?= $informant['informant_id'] ?>"
                        class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Back to Deceased Persons List
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center">
                            <!-- <div class="flex justify-between items-center gap-2">
                                <div class="px-3 py-4 rounded shadow text-black bg-skyblue">
                                    <h1 class="text-center text-2xl">
                                        Occupied Count
                                    </h1>
                                    <h1 class="text-center text-2xl">
                                        2
                                    </h1>
                                </div>
                                <div class="px-3 py-4 rounded shadow text-black bg-mutedYellow">
                                    <h1 class="text-center text-2xl">
                                        Occupied Count
                                    </h1>
                                    <h1 class="text-center text-2xl">
                                        1
                                    </h1>
                                </div>
                            </div> -->
                            <div>
                                <button data-dropdown-toggle="blocks-dropdown"
                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center"
                                        type="button">
                                    Block
                                    <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4"/>
                                    </svg>
                                </button>
                                <!-- Dropdown menu -->
                                <div id="blocks-dropdown"
                                    class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                                    <ul class="py-2 text-sm text-gray-700"
                                        aria-labelledby="blocks-dropdown">  
                                        <li>
                                            <a href="select_block.php?block=all&iId=<?= $informant['informant_id'] ?>"
                                            class="block px-4 py-2 hover:bg-gray-100">All</a>
                                        </li>
                                        <?php
                                        // Sample blocks array (replace this with your actual function call)
                                        $blocks = ['BLOCK1', 'BLOCK2', 'BLOCK3', 'BLOCK4', 'BLOCK5'];

                                        // Loop through blocks and generate list items
                                        foreach ($blocks as $block):
                                            ?>
                                            <li>
                                                <a href="select_block.php?block=<?= urlencode($block) ?>&iId=<?= $informant['informant_id'] ?>"
                                                class="block px-4 py-2 hover:bg-gray-100"><?= ucfirst(strtolower($block)) ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php if (empty($occupiedCounts)): ?>
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
                                <thead class="text-xs text-gray-700 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Block #</th>
                                    <th scope="col" class="px-6 py-3">Lot #</th>
                                    <th scope="col" class="px-6 py-3">Occupied Count</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 !py-4" colspan="4">No Data</td>
                                </tr>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
                                <thead class="text-xs text-gray-700 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Block #</th>
                                    <th scope="col" class="px-6 py-3">Lot #</th>
                                    <th scope="col" class="px-6 py-3">Count</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                                </thead>
                                <tbody id="table-body">
                                <?php foreach ($occupiedCounts as $count): ?>
                                    <?php
                                    $occupiedCount = isset($count['occupiedCount']) ? $count['occupiedCount'] : 0;
                                    
                                    // Skip rows where occupiedCount is exactly 2
                                    if ($occupiedCount == 2) {
                                        continue;
                                    }

                                    $bgClass = ($occupiedCount >= 1) ? (($occupiedCount >= 2) ? "bg-skyblue rounded" : "bg-mutedYellow rounded") : "hover:bg-gray-50";
                                    ?>
                                    <tr class="border-b <?= $bgClass ?>">
                                        <th scope="row" class="px-6 !py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <?= htmlspecialchars($count['blockNo']) ?>
                                        </th>
                                        <td class="px-4 !py-2"><?= htmlspecialchars($count['lotNo']) ?></td>
                                        <td class="px-4 !py-2"><?= htmlspecialchars($occupiedCount) ?></td>
                                        <td class="px-4 !py-2">
                                        <?php if ($occupiedCount < 2): ?>
                                            <a href="add_informant_deceased.php
                                                ?user_id=<?= $curUser?>
                                                &iId=<?= $informant['informant_id']?>
                                                &blockNo=<?= $count['blockNo']?>
                                                &lotNo=<?= $count['lotNo']?>"
                                                class="text-xs font-medium text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-info font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                Add Deceased Person
                                            </a>
                                        <?php else: ?>
                                            <p class="text-s text-grey-500 px-3 py-2">
                                                Lot is fully occupied.
                                            </p>
                                        <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- Pagination Controls -->
                            <div class="flex justify-between items-center mt-4">
                                <button id="prev" class="text-sm font-medium text-blue-700 border border-blue-700 rounded-lg px-4 py-2">Previous</button>
                                <div id="pagination-numbers" class="flex space-x-2"></div>
                                <button id="next" class="text-sm font-medium text-blue-700 border border-blue-700 rounded-lg px-4 py-2">Next</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <img src="<?= htmlspecialchars($mapDetails['URL']) ?>" alt="map" class="h-96 w-full rounded shadow">
                    </div>
                </div>
            </div>
        </div>
    </div>




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
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const entriesDropdown = document.getElementById('entries');
        const tableBody = document.getElementById('table-body');
        const rows = Array.from(tableBody.getElementsByTagName('tr'));
        const paginationNumbers = document.getElementById('pagination-numbers');
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');

        let currentPage = 1;
        let rowsPerPage = parseInt(entriesDropdown.value);

        function displayRows() {
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            updatePagination();
        }

        function updatePagination() {
            paginationNumbers.innerHTML = '';
            const totalPages = Math.ceil(rows.length / rowsPerPage);

            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = 'text-sm font-medium text-blue-700 border border-blue-700 rounded-lg px-4 py-2';
                if (i === currentPage) {
                    pageButton.classList.add('bg-blue-700', 'text-white');
                }
                pageButton.addEventListener('click', function () {
                    currentPage = i;
                    displayRows();
                });
                paginationNumbers.appendChild(pageButton);
            }

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
        }

        entriesDropdown.addEventListener('change', function () {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            displayRows();
        });

        prevButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                displayRows();
            }
        });

        nextButton.addEventListener('click', function () {
            const totalPages = Math.ceil(rows.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                displayRows();
            }
        });

        // Trigger change event on page load to display initial set of entries
        displayRows();
    });
    </script>

</body>

</html>