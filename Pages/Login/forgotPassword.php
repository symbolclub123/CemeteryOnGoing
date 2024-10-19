<?php
include "../../database/connectDatabase.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    $username = $_POST["username"];
    $identification = $_POST["identification_number"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if passwords match
    if ($password != $confirm_password) {
        $_SESSION["error"] = "Passwords do not match";
        header("Location: forgot_password.php");
        exit();
    }

    // Check if the user exists and the identification number matches
    $query = "SELECT * FROM users WHERE user_name = ? AND Identification = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $identification);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // User exists, update the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET user_password = ? WHERE user_name = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $username);
        mysqli_stmt_execute($update_stmt);

        if (mysqli_stmt_affected_rows($update_stmt) > 0) {
            $_SESSION["success"] = "Password successfully updated";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION["error"] = "Failed to Change the password";
        }

        mysqli_stmt_close($update_stmt);
    } else {
        $_SESSION["error"] = "Username or identification number does not match";
    }

    mysqli_stmt_close($stmt);

    // Redirect to the same page to implement PRG pattern
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Forgot Password</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100">
    <main class="h-screen w-full">
        <div class="h-screen flex justify-center items-center">
            <div class="w-96 bg-white border border-gray-200 rounded-lg shadow">
                <div class="flex flex-col items-center pt-5 px-5">
                    <a href="#" class="text-center">
                        <img class="rounded-t-lg mb-3 h-20 w-20" src="../../Assets/images/logo.png" alt="Logo " />
                    </a>
                    <h2 class="font-semibold">
                        PCC-IMS - Forgot Password
                    </h2>
                    <?php if (isset($_SESSION["error"])) { ?>
                        <p style="color: red"><?= $_SESSION["error"]; ?></p>
                    <?php unset($_SESSION["error"]); } ?>
                    <?php if (isset($_SESSION["success"])) { ?>
                        <p style="color: green"><?= $_SESSION["success"]; ?></p>
                    <?php unset($_SESSION["success"]); } ?>
                </div>

                <form class="p-5" action="#" method="post">
                    <div class="mb-3">
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900">Username</label>
                        <input type="text" id="username" name="username"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="identification_number"
                            class="block mb-2 text-sm font-medium text-gray-900">Employee ID</label>
                        <input type="text" id="identification_number" name="identification_number"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                        <input type="password" id="password" name="password" maxlength="16" minlength="8"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900">Confirm
                            password</label>
                        <input type="password" id="confirm_password" name="confirm_password" maxlength="16" minlength="8"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required />
                    </div>

                    <div class="buttons flex items-center justify-between">
                        <button
                            class=" px-3 py-2 text-sm font-medium text-center text-white bg-primary rounded-lg hover:bg-primaryDarker focus:ring-4 focus:outline-none focus:ring-primary"
                            type="submit">
                            Submit
                        </button>

                        <a href="login.php" class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-2 text-center">
                            Back to login
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
