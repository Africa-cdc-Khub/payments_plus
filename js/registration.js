// CPHIA 2025 Registration Form JavaScript

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Add a small delay to ensure all elements are rendered
    setTimeout(function() {
    const packageSelection = document.getElementById('packageSelection');
    const registrationForm = document.getElementById('registrationForm');
    const form = document.getElementById('registrationFormData');
    const packageCards = document.querySelectorAll('.package-card');
    const registrationTypeRadios = document.querySelectorAll('input[name="registration_type"]');
    const groupSizeSection = document.getElementById('groupSizeSection');
    const participantsSection = document.getElementById('participantsSection');
    const participantsContainer = document.getElementById('participantsContainer');
    const addParticipantBtn = document.getElementById('addParticipant');
    const addParticipantsNowCheckbox = document.getElementById('addParticipantsNow');
    const participantsDetails = document.getElementById('participantsDetails');
    const exhibitionDescriptionSection = document.getElementById('exhibitionDescriptionSection');
    const exhibitionDescriptionInput = document.getElementById('exhibition_description');
    const summarySection = document.getElementById('summarySection');
    const selectedPackageId = document.getElementById('selectedPackageId');
    const numPeopleInput = document.getElementById('numPeople');
    const nationalitySelect = document.getElementById('nationality');
    const changePackageBtn = document.getElementById('changePackage');
    const selectedPackageInfo = document.getElementById('selectedPackageInfo');
    
    let selectedPackage = null;
    let participantCount = 0;
    let countries = [];

    // Lobibox alert helper functions
    function showAlert(type, message, title = '') {
        Lobibox.alert(type, {
            title: title,
            msg: message,
            sound: false,
            delay: 3000,
            position: 'top right'
        });
    }

    function showSuccess(message, title = 'Success') {
        showAlert('success', message, title);
    }

    function showError(message, title = 'Error') {
        showAlert('error', message, title);
    }

    function showWarning(message, title = 'Warning') {
        showAlert('warning', message, title);
    }

    function showInfo(message, title = 'Information') {
        showAlert('info', message, title);
    }

    // Load countries data
    loadCountries();

    // Check if elements exist
    console.log('Package cards found:', packageCards.length);
    if (packageCards.length === 0) {
        console.error('No package cards found!');
        return;
    }
    
    console.log('Package selection element:', packageSelection);
    console.log('Registration form element:', registrationForm);

    // Package selection - make entire card clickable
    packageCards.forEach((card, index) => {
        console.log(`Adding click listener to card ${index}:`, card);
        
        card.addEventListener('click', function(e) {
            console.log(`Card ${index} clicked!`);
            selectPackage(card);
        });
        
        // Prevent double-clicking on the button
        const selectBtn = card.querySelector('.select-package');
        if (selectBtn) {
            console.log(`Adding button click listener to card ${index}`);
            selectBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                console.log(`Button clicked for card ${index}`);
                selectPackage(card);
            });
        } else {
            console.log(`No select button found for card ${index}`);
        }
    });

    // Change package button
    if (changePackageBtn) {
        changePackageBtn.addEventListener('click', function() {
            packageSelection.style.display = 'block';
            registrationForm.style.display = 'none';
            selectedPackage = null;
        });
    }

    function selectPackage(card) {
        try {
            console.log('Package card clicked:', card);
            
            // Store selected package data
            const priceText = card.querySelector('.package-price').textContent;
            const price = parseFloat(priceText.replace('$', '').replace(',', ''));
            
            console.log('Price text:', priceText, 'Parsed price:', price);
            
            selectedPackage = {
                id: card.dataset.packageId,
                name: card.dataset.packageName || card.querySelector('h5, h6').textContent,
                price: price,
                type: card.dataset.type,
                maxPeople: card.querySelector('.package-max') ? 
                    parseInt(card.querySelector('.package-max').textContent.match(/\d+/)[0]) : 1
            };
            
            console.log('Selected package:', selectedPackage);
            
            selectedPackageId.value = selectedPackage.id;
            
            // Update selected package info
            updateSelectedPackageInfo();
            
            // Show registration form
            console.log('Hiding package selection, showing registration form');
            packageSelection.style.display = 'none';
            registrationForm.style.display = 'block';
            
            // Set registration type based on package and enable/disable options
            const individualRadio = document.querySelector('input[name="registration_type"][value="individual"]');
            const groupRadio = document.querySelector('input[name="registration_type"][value="group"]');
            
            if (selectedPackage.type === 'individual') {
                // Individual package - enable both individual and group options
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = false;
                groupRadio.checked = false;
            } else if (selectedPackage.type === 'side_event') {
                // Side event package - only allow individual registration
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = true;
                groupRadio.checked = false;
            } else if (selectedPackage.type === 'exhibition') {
                // Exhibition package - only allow individual registration
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = true;
                groupRadio.checked = false;
            }
            
            // Trigger registration type change
            const event = new Event('change');
            if (individualRadio.checked) {
                individualRadio.dispatchEvent(event);
            } else if (groupRadio.checked) {
                groupRadio.dispatchEvent(event);
            }
            
            // Repopulate nationality select with filtered countries
            populateNationalitySelect();
        } catch (error) {
            console.error('Error in selectPackage:', error);
        }
    }

    function updateSelectedPackageInfo() {
        if (selectedPackage) {
            selectedPackageInfo.innerHTML = `
                <div class="selected-package-card">
                    <h4>${selectedPackage.name}</h4>
                    <p>${selectedPackage.type.charAt(0).toUpperCase() + selectedPackage.type.slice(1)} Package</p>
                    <div class="package-price">$${selectedPackage.price.toLocaleString()}</div>
                </div>
            `;
        }
    }

    function loadCountries() {
        fetch('data/countries.json')
            .then(response => response.json())
            .then(data => {
                countries = data;
                populateNationalitySelect();
            })
            .catch(error => {
                console.error('Error loading countries:', error);
                // Fallback to basic options
                nationalitySelect.innerHTML = `
                    <option value="">Select Nationality</option>
                    <option value="Ghanaian">Ghanaian</option>
                    <option value="Nigerian">Nigerian</option>
                    <option value="South African">South African</option>
                    <option value="Kenyan">Kenyan</option>
                    <option value="Other">Other</option>
                `;
            });
    }

    function populateNationalitySelect() {
        // Get the current selected value before clearing
        const currentValue = nationalitySelect.value;
        
        // Filter countries based on selected package
        const filteredCountries = selectedPackage ? 
            filterCountriesByPackage(selectedPackage.id, selectedPackage.name) : 
            countries;
        
        nationalitySelect.innerHTML = '<option value="">Select Nationality</option>';
        filteredCountries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.nationality;
            option.textContent = country.nationality;
            if (country.nationality === currentValue) {
                option.selected = true;
            }
            nationalitySelect.appendChild(option);
        });
        
        // Initialize Select2 after populating options
        initializeSelect2();
    }

    // Initialize Select2 for nationality dropdown
    function initializeSelect2() {
        $('#nationality').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Nationality',
            allowClear: true,
            width: '100%'
        });
        
        // Add change listener for nationality selection
        $('#nationality').on('change', function() {
            updatePricingBasedOnNationality();
            // Update cost estimation if group registration
            const numPeople = parseInt(numPeopleInput.value) || 0;
            if (numPeople > 0) {
                updateCostEstimation(numPeople);
            }
        });
    }

    // Function to check if nationality is African
    function isAfricanNational(nationality) {
        const africanNationalities = [
            'Algerian', 'Angolan', 'Beninese', 'Botswanan', 'Burkinabe', 'Burundian',
            'Cameroonian', 'Cape Verdian', 'Central African', 'Chadian', 'Comoran',
            'Congolese', 'Ivorian', 'Djibouti', 'Egyptian', 'Equatorial Guinean',
            'Eritrean', 'Ethiopian', 'Gabonese', 'Gambian', 'Ghanaian', 'Guinean',
            'Guinea-Bissauan', 'Kenyan', 'Lesotho', 'Liberian', 'Libyan', 'Malagasy',
            'Malawian', 'Malian', 'Mauritanian', 'Mauritian', 'Moroccan', 'Mozambican',
            'Namibian', 'Nigerien', 'Nigerian', 'Rwandan', 'Sao Tomean', 'Senegalese',
            'Seychellois', 'Sierra Leonean', 'Somali', 'South African', 'South Sudanese',
            'Sudanese', 'Swazi', 'Tanzanian', 'Togolese', 'Tunisian', 'Ugandan',
            'Zambian', 'Zimbabwean', 'Motswana', 'Mosotho'
        ];
        
        return africanNationalities.includes(nationality);
    }

    // Function to filter countries based on package type
    function filterCountriesByPackage(packageId, packageName) {
        if (!countries || countries.length === 0) return countries;
        
        let filteredCountries = countries;
        
        // Check package name for African/Non-African filtering
        if (packageName && packageName.toLowerCase().includes('african nationals')) {
            // African Nationals package - show only African countries
            filteredCountries = countries.filter(country => isAfricanNational(country.nationality));
        } else if (packageName && packageName.toLowerCase().includes('non african')) {
            // Non-African Nationals package - show only non-African countries
            filteredCountries = countries.filter(country => !isAfricanNational(country.nationality));
        }
        // For other packages (side events, exhibitions), show all countries
        
        return filteredCountries;
    }

    // Function to update pricing based on nationality
    function updatePricingBasedOnNationality() {
        const nationality = $('#nationality').val();
        if (!nationality || !selectedPackage) return;
        
        const isAfrican = isAfricanNational(nationality);
        const registrationType = document.querySelector('input[name="registration_type"]:checked');
        
        if (registrationType && registrationType.value === 'individual') {
            // Update package selection based on African status
            if (isAfrican) {
                // Show African pricing
                showInfo('You are eligible for African Nationals pricing ($200)', 'Pricing Update');
            } else {
                // Show Non-African pricing
                showInfo('Non-African Nationals pricing applies ($400)', 'Pricing Update');
            }
        } else if (registrationType && registrationType.value === 'group') {
            // Check all participant nationalities for group pricing
            checkGroupPricing();
        }
        
        updateSummary();
    }

    // Function to check group pricing based on all participants
    function checkGroupPricing() {
        const mainNationality = $('#nationality').val();
        const isMainAfrican = isAfricanNational(mainNationality);
        
        // Check participant nationalities
        let hasNonAfricanParticipants = false;
        const participantNationalities = document.querySelectorAll('.participant-nationality');
        
        participantNationalities.forEach(select => {
            const nationality = select.value;
            if (nationality && !isAfricanNational(nationality)) {
                hasNonAfricanParticipants = true;
            }
        });
        
        // Show appropriate pricing message
        if (hasNonAfricanParticipants || !isMainAfrican) {
            showWarning('Group includes non-African participants - Non-African pricing ($400 per person) will apply', 'Group Pricing');
        } else {
            showInfo('All participants are African nationals - African pricing ($200 per person) applies', 'Group Pricing');
        }
    }

    // Registration type change
    registrationTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Show/hide exhibition description based on package type
            if (selectedPackage && selectedPackage.type === 'exhibition') {
                exhibitionDescriptionSection.style.display = 'block';
                exhibitionDescriptionInput.required = false; // Make it optional
            } else {
                exhibitionDescriptionSection.style.display = 'none';
                exhibitionDescriptionInput.required = false;
            }
            
            if (this.value === 'group') {
                groupSizeSection.style.display = 'block';
                participantsSection.style.display = 'block';
                numPeopleInput.required = true;
                updateSummary();
            } else {
                groupSizeSection.style.display = 'none';
                participantsSection.style.display = 'none';
                numPeopleInput.required = false;
                participantsContainer.innerHTML = '';
                participantCount = 0;
                addParticipantsNowCheckbox.checked = false;
                participantsDetails.style.display = 'none';
                updateSummary();
            }
        });
    });

    // Add participants now checkbox
    if (addParticipantsNowCheckbox) {
        addParticipantsNowCheckbox.addEventListener('change', function() {
            if (this.checked) {
                participantsDetails.style.display = 'block';
                // Add initial participant form if none exist
                if (participantCount === 0) {
                    addParticipantForm();
                }
            } else {
                participantsDetails.style.display = 'none';
                participantsContainer.innerHTML = '';
                participantCount = 0;
            }
            updateSummary();
        });
    }

    // Number of people change
    if (numPeopleInput) {
        numPeopleInput.addEventListener('input', function() {
            const numPeople = parseInt(this.value) || 0;
            
            // Update cost estimation
            updateCostEstimation(numPeople);
            
            // Automatically add/remove participant fields
            updateParticipants(numPeople);
            updateSummary();
        });
    }

    // Add participant button (removed - now controlled automatically)
    // if (addParticipantBtn) {
    //     addParticipantBtn.addEventListener('click', function() {
    //         addParticipantForm();
    //     });
    // }

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!selectedPackage) {
            e.preventDefault();
            showError('Please select a package', 'Package Required');
            return;
        }

        if (!document.querySelector('input[name="registration_type"]:checked')) {
            e.preventDefault();
            showError('Please select a registration type', 'Registration Type Required');
            return;
        }

        const registrationType = document.querySelector('input[name="registration_type"]:checked').value;
        
        if (registrationType === 'group') {
            const numPeople = parseInt(numPeopleInput.value) || 0;
            if (numPeople < 1) {
                e.preventDefault();
                showError('Please enter the number of people for group registration', 'Group Size Required');
                return;
            }
            
            // Participants are optional - no validation needed
        }
        
        // Exhibition description is optional - no validation needed
        
        // Validate reCAPTCHA if enabled
        const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
        if (recaptchaResponse) {
            if (!recaptchaResponse.value) {
                e.preventDefault();
                showError('Please complete the reCAPTCHA verification', 'reCAPTCHA Required');
                return;
            }
        }
        
        // Handle side events and exhibition packages differently - no checkout, just confirmation
        if (selectedPackage && selectedPackage.type === 'side_event') {
            // This is a side event package - show special message
            showInfo('Side event registration will be confirmed via email. Payment will be processed after event approval.', 'Side Event Registration');
        } else if (selectedPackage && selectedPackage.type === 'exhibition') {
            // This is an exhibition package - show special message
            showInfo('Exhibition registration will be confirmed via email. Payment will be processed after exhibition approval.', 'Exhibition Registration');
        }
    });

    function updateCostEstimation(numPeople) {
        const costEstimation = document.getElementById('costEstimation');
        if (!costEstimation || !selectedPackage) return;
        
        if (numPeople < 1) {
            costEstimation.textContent = 'Enter number of people to see estimated cost';
            return;
        }
        
        // Check if this is a side event or exhibition package
        if (selectedPackage.type === 'side_event' || selectedPackage.type === 'exhibition') {
            // For side events and exhibitions, use exact package price
            const totalCost = selectedPackage.price;
            costEstimation.innerHTML = `
                <strong>Total: $${totalCost.toLocaleString()}</strong>
                <br><small class="text-muted">${selectedPackage.type === 'side_event' ? 'Side Event' : 'Exhibition'} package price</small>
            `;
            return;
        }
        
        // Calculate cost based on participant nationalities for regular packages
        const costBreakdown = calculateGroupCost(numPeople);
        
        if (costBreakdown.hasParticipants) {
            // Show detailed breakdown based on actual participant nationalities
            costEstimation.innerHTML = `
                <strong>Total: $${costBreakdown.totalCost.toLocaleString()}</strong>
                <br><small class="text-muted">
                    ${costBreakdown.africanCount} African × $200 = $${costBreakdown.africanCost.toLocaleString()}
                    ${costBreakdown.nonAfricanCount > 0 ? `<br>${costBreakdown.nonAfricanCount} Non-African × $400 = $${costBreakdown.nonAfricanCost.toLocaleString()}` : ''}
                </small>
            `;
        } else {
            // Show estimated cost based on main registrant nationality
            const nationality = $('#nationality').val();
            const isAfrican = nationality ? isAfricanNational(nationality) : false;
            const actualPrice = isAfrican ? 200 : 400;
            const totalCost = actualPrice * numPeople;
            
            costEstimation.innerHTML = `
                <strong>${numPeople} people × $${actualPrice.toLocaleString()} = $${totalCost.toLocaleString()}</strong>
                <br><small class="text-muted">Estimated based on main registrant nationality</small>
            `;
        }
    }

    function calculateGroupCost(numPeople) {
        const participantNationalities = document.querySelectorAll('.participant-nationality');
        let africanCount = 0;
        let nonAfricanCount = 0;
        
        // Count nationalities from participant forms
        participantNationalities.forEach(select => {
            const nationality = select.value;
            if (nationality) {
                if (isAfricanNational(nationality)) {
                    africanCount++;
                } else {
                    nonAfricanCount++;
                }
            }
        });
        
        // If we have participant data, use it
        if (africanCount > 0 || nonAfricanCount > 0) {
            const africanCost = africanCount * 200;
            const nonAfricanCost = nonAfricanCount * 400;
            const totalCost = africanCost + nonAfricanCost;
            
            return {
                hasParticipants: true,
                africanCount: africanCount,
                nonAfricanCount: nonAfricanCount,
                africanCost: africanCost,
                nonAfricanCost: nonAfricanCost,
                totalCost: totalCost
            };
        }
        
        // If no participant data yet, estimate based on main registrant
        const nationality = $('#nationality').val();
        const isAfrican = nationality ? isAfricanNational(nationality) : false;
        
        if (isAfrican) {
            return {
                hasParticipants: false,
                africanCount: numPeople,
                nonAfricanCount: 0,
                africanCost: numPeople * 200,
                nonAfricanCost: 0,
                totalCost: numPeople * 200
            };
        } else {
            return {
                hasParticipants: false,
                africanCount: 0,
                nonAfricanCount: numPeople,
                africanCost: 0,
                nonAfricanCost: numPeople * 400,
                totalCost: numPeople * 400
            };
        }
    }

    function updateParticipants(numPeople) {
        if (numPeople < 1) {
            // Clear all participant fields
            participantsContainer.innerHTML = '';
            participantCount = 0;
            return;
        }
        
        const currentCount = participantsContainer.querySelectorAll('.participant-card').length;
        
        if (numPeople > currentCount) {
            // Add participants (max 10 for UI)
            const formsToAdd = Math.min(numPeople, 10) - currentCount;
            for (let i = 0; i < formsToAdd; i++) {
                addParticipantForm();
            }
            
            if (numPeople > 10) {
                showInfo(`You can add details for up to 10 participants now. The remaining ${numPeople - 10} participants can be added later via email.`, 'Participant Details');
            }
        } else if (numPeople < currentCount) {
            // Remove participants
            const participants = participantsContainer.querySelectorAll('.participant-card');
            for (let i = currentCount - 1; i >= numPeople; i--) {
                participants[i].remove();
            }
            participantCount = Math.min(participantCount, numPeople);
        }
    }

    function addParticipantForm() {
        participantCount++;
        const participantDiv = document.createElement('div');
        participantDiv.className = 'card mb-3 participant-card';
        participantDiv.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">Participant ${participantCount}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Title</label>
                        <select name="participants[${participantCount - 1}][title]" class="form-select">
                            <option value="">Select</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Ms.">Ms.</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][first_name]" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][last_name]" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="participants[${participantCount - 1}][email]" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Passport Number</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][passport_number]">
                        <div class="form-text small">Optional - for international participants</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Passport Copy (PDF)</label>
                        <input type="file" class="form-control" name="participants[${participantCount - 1}][passport_file]" accept=".pdf">
                        <div class="form-text small">Upload passport copy (PDF, max 5MB)</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="participants[${participantCount - 1}][requires_visa]" value="1">
                            <label class="form-check-label">
                                Requires visa to enter Ghana?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nationality *</label>
                        <select name="participants[${participantCount - 1}][nationality]" class="form-select participant-nationality" required>
                            <option value="">Select Nationality</option>
                            ${(selectedPackage ? filterCountriesByPackage(selectedPackage.id, selectedPackage.name) : countries).map(country => `<option value="${country.nationality}">${country.nationality}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Organization *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][organization]" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Position/Title</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][position]">
                    </div>
                </div>
            </div>
        `;
        
        participantsContainer.appendChild(participantDiv);
        
        // Initialize Select2 for the new nationality dropdown
        $(participantDiv).find('.participant-nationality').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Nationality',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            // Check group pricing when participant nationality changes
            const registrationType = document.querySelector('input[name="registration_type"]:checked');
            if (registrationType && registrationType.value === 'group') {
                checkGroupPricing();
                // Update cost estimation based on participant nationalities
                const numPeople = parseInt(numPeopleInput.value) || 0;
                if (numPeople > 0) {
                    updateCostEstimation(numPeople);
                }
            }
        });
    }

    // removeParticipant function removed - participants are now automatically managed

    function updateSummary() {
        if (!selectedPackage) {
            summarySection.style.display = 'none';
            return;
        }

        const registrationType = document.querySelector('input[name="registration_type"]:checked');
        if (!registrationType) {
            summarySection.style.display = 'none';
            return;
        }

        summarySection.style.display = 'block';
        
        // Determine actual pricing based on nationality
        let actualPackage = selectedPackage;
        let pricingNote = '';
        
        if (registrationType.value === 'individual') {
            const nationality = $('#nationality').val();
            if (nationality) {
                const isAfrican = isAfricanNational(nationality);
                if (isAfrican) {
                    actualPackage = { name: 'African Nationals', price: 200 };
                    pricingNote = ' (African pricing)';
                } else {
                    actualPackage = { name: 'Non-African Nationals', price: 400 };
                    pricingNote = ' (Non-African pricing)';
                }
            }
        } else if (registrationType.value === 'group') {
            const mainNationality = $('#nationality').val();
            const isMainAfrican = isAfricanNational(mainNationality);
            
            // Check participant nationalities
            let hasNonAfricanParticipants = false;
            const participantNationalities = document.querySelectorAll('.participant-nationality');
            
            participantNationalities.forEach(select => {
                const nationality = select.value;
                if (nationality && !isAfricanNational(nationality)) {
                    hasNonAfricanParticipants = true;
                }
            });
            
            if (hasNonAfricanParticipants || !isMainAfrican) {
                actualPackage = { name: 'Group Registration (Non-African)', price: 400 };
                pricingNote = ' (Non-African pricing - mixed group)';
            } else {
                actualPackage = { name: 'Group Registration (African)', price: 200 };
                pricingNote = ' (African pricing)';
            }
        }
        
        document.getElementById('summaryPackage').textContent = actualPackage.name + pricingNote;
        document.getElementById('summaryType').textContent = registrationType.value.charAt(0).toUpperCase() + registrationType.value.slice(1);
        
        let numPeople = 1;
        if (registrationType.value === 'group') {
            numPeople = parseInt(numPeopleInput.value) || 1;
        }
        
        document.getElementById('summaryPeople').textContent = numPeople;
        
        // Calculate total amount based on package type
        let totalAmount;
        if (selectedPackage.type === 'side_event' || selectedPackage.type === 'exhibition') {
            // For side events and exhibitions, use exact package price
            totalAmount = selectedPackage.price;
        } else if (registrationType.value === 'group') {
            // For regular group registration, calculate based on participant nationalities
            const costBreakdown = calculateGroupCost(numPeople);
            totalAmount = costBreakdown.totalCost;
        } else {
            // For individual registration, use nationality-based pricing
            totalAmount = actualPackage.price * numPeople;
        }
        
        document.getElementById('summaryTotal').textContent = '$' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    // removeParticipant function removed - participants are now automatically managed
    
    // Restore form data on page load (for error handling)
    function restoreFormData() {
        // Check if we have form data to restore (from PHP)
        const formData = window.formData || {};
        
        if (Object.keys(formData).length > 0) {
            console.log('Restoring form data:', formData);
            
            // Restore basic fields
            if (formData.first_name) document.getElementById('first_name').value = formData.first_name;
            if (formData.last_name) document.getElementById('last_name').value = formData.last_name;
            if (formData.email) document.getElementById('email').value = formData.email;
            if (formData.phone) document.getElementById('phone').value = formData.phone;
            if (formData.organization) document.getElementById('organization').value = formData.organization;
            if (formData.position) document.getElementById('position').value = formData.position;
            if (formData.passport_number) document.getElementById('passport_number').value = formData.passport_number;
            if (formData.passport_file) document.getElementById('passport_file').value = formData.passport_file;
            if (formData.requires_visa) document.getElementById('requires_visa').checked = true;
            if (formData.address_line1) document.getElementById('address_line1').value = formData.address_line1;
            if (formData.address_line2) document.getElementById('address_line2').value = formData.address_line2;
            if (formData.city) document.getElementById('city').value = formData.city;
            if (formData.state) document.getElementById('state').value = formData.state;
            if (formData.country) document.getElementById('country').value = formData.country;
            if (formData.postal_code) document.getElementById('postal_code').value = formData.postal_code;
            if (formData.exhibition_description) document.getElementById('exhibition_description').value = formData.exhibition_description;
            
            // Restore registration type
            if (formData.registration_type) {
                const registrationTypeRadio = document.querySelector(`input[name="registration_type"][value="${formData.registration_type}"]`);
                if (registrationTypeRadio) {
                    registrationTypeRadio.checked = true;
                    handleRegistrationTypeChange();
                }
            }
            
            // Restore package selection
            if (formData.package_id) {
                const packageCard = document.querySelector(`[data-package-id="${formData.package_id}"]`);
                if (packageCard) {
                    selectPackage(packageCard);
                }
            }
            
            // Restore number of people for group registration
            if (formData.num_people && formData.registration_type === 'group') {
                document.getElementById('numPeople').value = formData.num_people;
                updateParticipants();
            }
            
            // Restore nationality after countries are loaded
            if (formData.nationality) {
                // Wait for countries to load, then set nationality
                const checkNationality = () => {
                    if (countries.length > 0) {
                        document.getElementById('nationality').value = formData.nationality;
                        // Trigger Select2 update
                        $('#nationality').trigger('change');
                    } else {
                        setTimeout(checkNationality, 100);
                    }
                };
                checkNationality();
            }
            
            // Restore participants data
            if (formData.participants && Array.isArray(formData.participants)) {
                formData.participants.forEach((participant, index) => {
                    if (index > 0) { // First participant is the main form
                        addParticipant();
                        const participantContainer = document.querySelector(`[data-participant-index="${index}"]`);
                        if (participantContainer) {
                            if (participant.title) participantContainer.querySelector('input[name*="[title]"]').value = participant.title;
                            if (participant.first_name) participantContainer.querySelector('input[name*="[first_name]"]').value = participant.first_name;
                            if (participant.last_name) participantContainer.querySelector('input[name*="[last_name]"]').value = participant.last_name;
                            if (participant.email) participantContainer.querySelector('input[name*="[email]"]').value = participant.email;
                            if (participant.nationality) participantContainer.querySelector('select[name*="[nationality]"]').value = participant.nationality;
                            if (participant.passport_number) participantContainer.querySelector('input[name*="[passport_number]"]').value = participant.passport_number;
                            if (participant.passport_file) participantContainer.querySelector('input[name*="[passport_file]"]').value = participant.passport_file;
                            if (participant.requires_visa) participantContainer.querySelector('input[name*="[requires_visa]"]').checked = true;
                            if (participant.organization) participantContainer.querySelector('input[name*="[organization]"]').value = participant.organization;
                            if (participant.position) participantContainer.querySelector('input[name*="[position]"]').value = participant.position;
                        }
                    }
                });
            }
            
            // Update summary
            updateSummary();
        }
    }
    
    // Call restore function after a short delay to ensure everything is loaded
    setTimeout(restoreFormData, 200);
    
    }, 100); // End of setTimeout
}); // End of DOMContentLoaded
