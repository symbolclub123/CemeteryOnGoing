<?php
include "../../database/connectDatabase.php";
session_start();

// Initialize login attempts and lockout status
if (!isset($_SESSION["login_attempts"])) {
    $_SESSION["login_attempts"] = 0;
}

if (!isset($_SESSION["locked"])) {
    $_SESSION["locked"] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Check if user is currently locked out
    if (time() - $_SESSION["locked"] < 30) {
        $_SESSION["error"] = "You must wait for 30 seconds before trying again.";
    } else {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Use prepared statements to prevent SQL injection
        $query = "SELECT user_id, user_password FROM users WHERE user_name = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['user_password'])) {
                // Password is correct, start the session and redirect
                $_SESSION["user_id"] = $row['user_id'];
                $_SESSION["login_attempts"] = 0; // Reset login attempts
                header("Location: ../Backend/dashboard.php"); // Redirect to the dashboard or another page
                exit();
            } else {
                $_SESSION["login_attempts"] += 1;
                $_SESSION["error"] = "Invalid username or password"; // Avoid revealing specific error
            }
        } else {
            $_SESSION["login_attempts"] += 1;
            $_SESSION["error"] = "Invalid username or password"; // Avoid revealing specific error
        }

        // If login attempts exceed 2, lock the user out for an incremental duration
        if ($_SESSION["login_attempts"] > 2) {
            $_SESSION["locked"] = time() + (60 * $_SESSION["login_attempts"]); // Incremental lockout duration
            $_SESSION["error"] = "You must wait for " . (60 * $_SESSION["login_attempts"]) . " seconds before trying again.";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    }

    // Redirect to the same page to implement PRG pattern
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PCC-IMS - Login</title>
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
                        <img class="rounded-t-lg mb-3 h-20 w-20" src="../../Assets/images/logo.png" alt="Logo" />
                    </a>
                    <h2 class="font-semibold">
                        PCC-IMS - Login
                    </h2>
                    <?php if (isset($_SESSION["error"])) { ?>
                        <p style="color: red"><?= $_SESSION["error"]; ?></p>
                    <?php unset($_SESSION["error"]); } ?>
                </div>
                <form class="p-5" action="#" method="post">
                    <div class="relative z-0 mb-3">
                        <input type="text" id="username" name="username"
                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                            placeholder=" " />
                        <label for="username"
                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Username</label>
                    </div>
                    <div class="relative z-0 mb-5">
                        <input type="password" id="password" name="password"
                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                            placeholder=" " />
                        <label for="password"
                            class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Password</label>
                    </div>
                    <div class="buttons space-y-8 flex flex-col items-center">
                        <?php
                            if (time() - $_SESSION["locked"] < 30) {
                                echo "<p>Please wait for 30 seconds</p>";
                            } else {
                        ?>
                        <button
                            class="w-full px-3 py-2 text-sm font-medium text-center text-white bg-primary rounded-lg hover:bg-primaryDarker focus:ring-4 focus:outline-none focus:ring-primary"
                            type="submit">
                            Login
                        </button>
                        <?php } ?>
                            <a href="../Home.php" class="font-medium text-blue-600 hover:underline">Home Page</a>
                        <div>
                            <a href="forgotPassword.php" class="font-medium text-blue-600 hover:underline">Forgot password</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>
