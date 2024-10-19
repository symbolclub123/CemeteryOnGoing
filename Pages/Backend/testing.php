<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <style>
        /* Simple modal styles */
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
    </style>
</head>
<body>

<form action="#" method="post">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border rounded p-3">
                            <h1 class=" text-xl mb-3 text-black font-bold">Personal
                                Information</h1>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div class="mb-3">
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
                                    <label for="province" class="block mb-2 text-sm font-medium text-gray-900">Province</label>
                                    <select id="province" name="province" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        <option value="">Select Province</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="municipality" class="block mb-2 text-sm font-medium text-gray-900">City/Municipality</label>
                                    <select id="municipality" name="municipality" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        <option value="">Select City/Municipality</option>
                                    </select>
                                </div>
                            </div>
                        
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div>
                                    <label for="barangay" class="block mb-2 text-sm font-medium text-gray-900">Barangay</label>
                                    <select id="barangay" name="barangay" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        <option value="">Select Barangay</option>
                                    </select>
                                </div>
                                
                            <!-- <div class="grid grid-cols-2 gap-2 mb-3">
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
                                    
                                </div> -->
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
                            <h1 class="text-xl mb-3 text-black font-bold">
                                Death Information
                            </h1>
                            <div>
                                <label for="ref_number" class="block mb-2 text-sm font-medium text-gray-900">
                                    Death Certification Reference Number
                                </label>
                                <input type="text" id="ref_number" 
                                        name="ref_number"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="" required />
                            </div><br>
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
                        <a href="trial_select_lot.php?user_id=<?= $curUser?>&iId=<?= $informant['informant_id']?>&block=<?= $block?>"
                            class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ms-2">
                            Back to Lot Selection
                        </a>
                    </div>
                </form>
                                        

                <script>
    const provinceSelect = document.getElementById('province');
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');

    // Fetch provinces from PSGC API
    async function fetchProvinces() {
        const response = await fetch('https://psgc.gitlab.io/api/provinces/');
        const provinces = await response.json();
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        provinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.code;
            option.textContent = province.name;
            provinceSelect.appendChild(option);
        });
    }

    // Fetch municipalities for a selected province
    async function fetchMunicipalities(provinceCode) {
        const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/municipalities`);
        const municipalities = await response.json();
        municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>'; // Clear barangays
        municipalities.forEach(municipality => {
            const option = document.createElement('option');
            option.value = municipality.code;
            option.textContent = municipality.name;
            municipalitySelect.appendChild(option);
        });
    }

    // Fetch barangays for a selected municipality
    async function fetchBarangays(municipalityCode) {
        const response = await fetch(`https://psgc.gitlab.io/api/municipalities/${municipalityCode}/barangays`);
        const barangays = await response.json();
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay.code;
            option.textContent = barangay.name;
            barangaySelect.appendChild(option);
        });
    }

    // Event listener for province change
    provinceSelect.addEventListener('change', function() {
        const selectedProvince = this.value;
        if (selectedProvince) {
            fetchMunicipalities(selectedProvince);
        } else {
            municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        }
    });

    // Event listener for municipality change
    municipalitySelect.addEventListener('change', function() {
        const selectedMunicipality = this.value;
        if (selectedMunicipality) {
            fetchBarangays(selectedMunicipality);
        } else {
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        }
    });

    // Initialize by fetching provinces on page load
    fetchProvinces();
</script>

</body>
</html>
