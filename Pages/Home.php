<?php

session_start();

// Include your database connection file
include "../database/connectDatabase.php";

// Function to get deceased persons information with informant names
function getDeceasedPersons()
{
    global $conn;

    $query = "SELECT dr.*, i.fullname AS informant_name
              FROM deceased_records dr
              LEFT JOIN informants i ON dr.informant_id = i.informant_id
              WHERE dr.status = 'OCCUPIED'
              ORDER BY block ASC, lots DESC, last_name ASC";

    $result = mysqli_query($conn, $query);

    $deceasedPersons = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $deceasedPersons[] = $row;
    }

    return $deceasedPersons;
}

/*// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["archive_id"])) {
    $deceasedPersonId = mysqli_real_escape_string($conn, $_POST["archive_id"]);

    // Update the statuss to ARCHIVE
    $updateQuery = "UPDATE deceased_persons SET statuss = 'ARCHIVE' WHERE deceased_person_id = '$deceasedPersonId'";
    $result = mysqli_query($conn, $updateQuery);

    if ($result) {
        // Redirect to the same page after updating
        header("Location: Memorial.php");
        exit();
    } else {
        echo "Error updating statuss";
    }
}*/

$deceasedPersons = getDeceasedPersons();

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PCC-IMS</title>
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

  <link rel="stylesheet" href="../Assets/datatables/dataTables.tailwindcss.min.css" />
  <link rel="stylesheet" href="../Assets/jquery/jquery.dataTables.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100">
  <header>
    <nav class="bg-primary fixed w-full z-20 top-0 start-0">
      <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <a href="#home" class="flex items-center space-x-3 rtl:space-x-reverse">
          <img src="../Assets/images/logo.png" class="h-10 bg-white rounded-full" alt="Logo" loading="lazy" />

          <span class="self-center text-2xl font-semibold whitespace-nowrap text-white hidden lg:block">
          Calauan Public Cemetery
          </span>
        </a>
        <!-- <div class="flex md:order-2 block tablet:none">
          <a href="#"
            class="text-white border border-white hover:text-primary hover:bg-white focus:bg-white focus:ring-4 focus:outline-none focus:ring-primary font-medium rounded-lg text-sm px-4 py-2 text-center bg-transparent transition-colors duration-300">
            Login
          </a>


        </div> -->
        <button data-collapse-toggle="navbar-search" type="button"
          class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-white focus:outline-none focus:ring-2 focus:ring-white-200 text-white hovber:text-primary focus:text-primary hover:bg-white focus:ring-gray-600"
          aria-controls="navbar-search" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M1 1h15M1 7h15M1 13h15"></path>
          </svg>
        </button>
        <div class="hidden w-full md:block md:w-auto" id="navbar-search">
          <ul class="flex flex-col p-4 md:p-0 mt-4 font-medium md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0">
            <li>
              <a href="#home"
                class="block py-1 px-2 mt-2 sm:mt-0 rounded hover:bg-white hover:text-primary text-white"
                aria-current="page">Home</a>
            </li>
            <li>
              <a href="#about"
                class="block py-1 px-2 mt-2 sm:mt-0 rounded hover:bg-white hover:text-primary text-white">
                About
              </a>
            </li>
            <li>
              <a href="#rules"
                class="block py-1 px-2 mt-2 sm:mt-0 rounded hover:bg-white hover:text-primary text-white">
                Rules
              </a>
            </li>
            <li>
              <a href="#memorials"
                class="block py-1 px-2 mt-2 sm:mt-0 rounded hover:bg-white hover:text-primary text-white">
                Memorials
              </a>
            </li>
            <li>
              <a href="Login/login.php"
                class="block py-1 px-2 mt-2 sm:mt-0 rounded hover:bg-white hover:text-primary text-white">
                Login
              </a>
            </li>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="h-screen w-full">
    <section
      class="bg-center bg-no-repeat bg-cover bg-[url('../Assets/images/cemetery.jpg')] bg-gray-400 bg-blend-multiply h-screen"
      id="home">
      <div class="px-5 sm:px-10 lg:px-20 mx-auto text-center py-24 lg:py-56">
        <h1
          class="mb-4 mt-20 tablet:mt-0 text-2xl font-extrabold tracking-tight leading-none text-white md:text-4xl lg:text-5xl">
          Calauan Public Cemetery

        </h1>
        <h1 class="mb-4 text-2xl font-extrabold tracking-tight leading-none text-white md:text-4xl lg:text-5xl">
          Information Management System
        </h1>
        <p class="mb-8 text-lg font-normal text-gray-300 lg:text-xl sm:px-16 lg:px-48">
        "Honoring Lives, Preserving Legacies: The Heart of Every Cemetery System - 
        Where Every Resting Place Tells a Story, Cherishing Memories and Celebrating 
        the Lives of Those Who Came Before Us."
        </p>
      </div>
    </section>

    <section class="container mx-auto px-5 mb-[50px]" id="about">
      <h1 class="text-center text-2xl font-semibold md:text-3xl lg:text-4xl text-primary mb-10 tablet:mb-20 pt-20">
        About
      </h1>
      <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4 justify-center items-center">
        <div class="order-2 tablet:order-1 w-full tablet:h-full max-h-full shadow-md sm:rounded-lg">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d967.228432929685!2d121.31072216963632!3d14.14116919914331!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMTTCsDA4JzI4LjIiTiAxMjHCsDE4JzQwLjkiRQ!5e0!3m2!1sen!2sph!4v1718544541160!5m2!1sen!2sph"
            height="400" style="border: 0; width: 100%" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade" class="tablet:h-full"></iframe>
        </div>
        <div class="order-1 tablet:order-2 w-full">
          <div class="shadow-md sm:rounded-lg bg-white p-5">
            <h1 class="text-center text-2xl font-semibold md:text-3xl lg:text-4xl mb-5">
              <span class="text-black"> Calauan </span>
              <span class="text-primary"> Public Cemetery </span>
            </h1>
            <p class="text-justify md:px-10 mb-5">
            The Calauan Public Cemetery is very important as the final resting 
            place for people in the Calauan community. Right now, burial details
             are recorded in logbooks and Excel spreadsheets. This study looks 
             at the historical and community importance of the cemetery. The 
             cemetery is facing a big problem with too many burials, leading to
              crowded gravesites and not enough space. This situation affects 
              the dignity of the deceased and makes it hard to manage the 
              cemetery properly. The lack of a better record-keeping system 
              adds to these challenges, making it difficult to organize burial 
              plots and keep accurate records. Our study aims to show these 
              problems and suggest ways to improve the management of the cemetery
              , ensuring it respects and honors the memories of the deceased.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="container mx-auto px-5 mb-[50px]" id="rules">
      <h1 class="text-center text-2xl font-semibold md:text-3xl lg:text-4xl text-primary mb-10 tablet:mb-20 pt-20">
        Rules
      </h1>
      <div class="flex justify-center items-center">
        <div class="w-fit flex flex-col justify-center items-center bg-white shadow p-10 md:p-16">
          <h1 class="text-center text-2xl font-semibold mb-5">
            The following guidelines are prohibited inside the Cemetery
          </h1>

          <div class="grid grid-cols-1 md:grid-cols-2  grid-rows-2 gap-2">
            <div>
              <div class="rounded-text-center flex items-center">
                <img src="../Assets/images/shooting.png" alt="weapons" srcset="weapons icon"
                  style="height: 40px; width: 40px" />
                <h2 class="font-semibold ms-3">
                  Firearms and any sharp objects
                </h2>
              </div>
            </div>
            <div>
              <div class="rounded-text-center flex items-center">
                <img src="../Assets/images/flammable.png" alt="flammable" srcset="flammable icon"
                  style="height: 40px; width: 40px" />
                <h2 class="font-semibold ms-3">Flammable materials</h2>
              </div>
            </div>
            <div>
              <div class="rounded-text-center flex items-center">
                <img src="../Assets/images/no-drinks.png" alt="drinks" srcset="drinks icon"
                  style="height: 40px; width: 40px" />
                <h2 class="font-semibold ms-3">Alcoholic beverages</h2>
              </div>
            </div>
            <div>
              <div class="rounded-text-center flex items-center">
                <img src="../Assets/images/forbidden-sign.png" alt="drinks" srcset="drinks icon"
                  style="height: 40px; width: 40px" />
                <h2 class="font-semibold ms-3">
                  Videoke or any sound system that will cause loud sounds
                </h2>
              </div>
            </div>
          </div>
        </div>
      </div><br><br><br><br>

    </section>
    <section class="container mx-auto px-5 pb-52" id="memorials">
      <h1 class="text-center text-2xl font-semibold md:text-3xl lg:text-4xl text-primary mb-10 tablet:mb-20 pt-20">
        Memorials
      </h1>
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg bg-white p-7">
        <?php if (empty($deceasedPersons)): ?>
                  <p>No deceased persons found.</p>
        <?php else: ?>
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 data-table">
            <thead class="text-xs text-gray-700 uppercase">
                <tr>
                  <th scope="col" class="px-6 py-3">Last Name</th>
                  <th scope="col" class="px-6 py-3">First Name</th>
                  <th scope="col" class="px-6 py-3">Middle Name</th>
                  <!-- <th scope="col" class="px-6 py-3">Date of Death</th> -->
                  <th scope="col" class="px-6 py-3">Block Number</th>
                  <th scope="col" class="px-6 py-3">Lot Number</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deceasedPersons as $deceasedPerson): ?>
                    <tr>
                        <td scope="col" class="px-6 py-3"><?= $deceasedPerson['last_name'] ?></td>
                        <td scope="col" class="px-6 py-3"><?= $deceasedPerson['first_name'] ?></td>
                        <td scope="col" class="px-6 py-3"><?= $deceasedPerson['middle_name'] ?></td>
                        <!-- <td scope="col" class="px-6 py-3"><?= $deceasedPerson['dateOfdeath'] ?></td> -->
                        <td scope="col" class="px-6 py-3"><?= $deceasedPerson['block'] ?></td>
                        <td scope="col" class="px-6 py-3"><?= $deceasedPerson['lots'] ?></td>
                    </tr>    
                <?php endforeach; ?>                            
            </tbody>
        </table>     
        <?php endif; ?>                
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  <script src="../Assets/jquery/jquery.min.js"></script>
  <script src="../Assets/datatables/jquery.datatables.min.js"></script>
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