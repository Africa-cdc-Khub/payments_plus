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
    let nationalities = [];
    let africanNationalities = [];

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

    // Load countries data and initialize form
    loadCountries().then(() => {
        console.log('Countries loaded, initializing form');
        
        // Load African nationalities for filtering
        return loadNationalities(null, 'African Nationals');
    }).then(africanNats => {
        africanNationalities = africanNats.map(nat => nat.nationality);
        console.log('African nationalities loaded:', africanNationalities.length);
        
        // Initialize nationality dropdown (populated server-side)
        populateNationalitySelect();
        
        // Initialize email validation
        initializeEmailValidation();
        
        // Initialize field requirements based on current state
        initializeFieldRequirements();
        
        // Restore package selection if form data exists
        restorePackageSelection();
    }).catch(error => {
        console.error('Error loading countries nationalities:', error);
    });

    // Check if elements exist
    console.log('Package cards found:', packageCards.length);
    if (packageCards.length === 0) {
        console.error('No package cards found!');
        return;
    }
    
    console.log('Package selection element:', packageSelection);
    console.log('Registration form element:', registrationForm);
    
    // Check if form is already visible due to validation errors
    const isFormVisible = registrationForm.style.display === 'block' || 
                         window.getComputedStyle(registrationForm).display === 'block';
    
    if (isFormVisible) {
        console.log('Form is already visible (likely due to validation errors)');
        // Restore form data if available
        restoreFormData();
    }

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
            
            // Hide package description
            const packageDescription = document.getElementById('packageDescription');
            if (packageDescription) {
                packageDescription.style.display = 'none';
            }
        });
    }

    function selectPackage(card) {
        try {
            console.log('Package card clicked:', card);
            
            // Store selected package data
            const priceElement = card.querySelector('.package-price');
            const price = priceElement ? parseFloat(priceElement.textContent.replace('$', '').replace(',', '')) : 0;
            
            console.log('Price element:', priceElement, 'Parsed price:', price);
            
            // Get icon and color from the package card
            const iconElement = card.querySelector('.package-icon i');
            const icon = iconElement ? iconElement.className.split(' ').slice(0, 2).join(' ') : 'fas fa-ticket-alt';
            const color = iconElement ? iconElement.className.split(' ').slice(2, 3).join(' ') : 'text-primary';
            
            selectedPackage = {
                id: card.dataset.packageId,
                name: card.dataset.packageName || card.querySelector('h5, h6').textContent,
                price: price,
                type: card.dataset.type,
                continent: (card.dataset.continent || 'all').toLowerCase(),
                icon: icon,
                color: color,
                maxPeople: card.querySelector('.package-max') ? 
                    parseInt(card.querySelector('.package-max').textContent.match(/\d+/)[0]) : 1
            };
            
            console.log('Selected package:', selectedPackage);
            console.log('Package name from dataset:', card.dataset.packageName);
            console.log('Package name from text content:', card.querySelector('h5, h6')?.textContent);
            
            selectedPackageId.value = selectedPackage.id;
            
            // Update selected package info
            updateSelectedPackageInfo();
            
            // Show package description
            showPackageDescription();
            
            // Show registration form
            console.log('Hiding package selection, showing registration form');
            packageSelection.style.display = 'none';
            registrationForm.style.display = 'block';
            
            // Set registration type based on package and enable/disable options
            const individualRadio = document.querySelector('input[name="registration_type"][value="individual"]');
            const groupRadio = document.querySelector('input[name="registration_type"][value="group"]');
            
            if (selectedPackage.type === 'individual') {
                // Check if this is Students or Delegates package
                if (selectedPackage.name.toLowerCase() === 'students' || selectedPackage.name.toLowerCase() === 'delegates') {
                    // Students/Delegates package - only allow individual registration
                    console.log('Students/Delegates package selected - disabling group registration');
                    individualRadio.checked = true;
                    individualRadio.disabled = false;
                    groupRadio.disabled = true;
                    groupRadio.checked = false;
                    
                    // Add visual indication that group registration is not available
                    const groupLabel = document.querySelector('label[for="group"]');
                    if (groupLabel) {
                        groupLabel.style.opacity = '0.5';
                        groupLabel.style.cursor = 'not-allowed';
                        groupLabel.title = 'Group registration is not available for ' + selectedPackage.name + ' package';
                    }
                    
                    // Show student fields for Students package
                    if (selectedPackage.name.toLowerCase() === 'students') {
                        // Hide organization fields for students
                        const organizationFields = document.getElementById('organizationFields');
                        if (organizationFields) {
                            organizationFields.style.display = 'none';
                        }
                        
                        // Set default values for organization and position
                        const organizationField = document.getElementById('organization');
                        const positionField = document.getElementById('position');
                        if (organizationField) {
                            organizationField.value = 'N/A';
                            organizationField.required = false;
                        }
                        if (positionField) {
                            positionField.value = 'N/A';
                        }
                        
                        // Show student fields
                        const studentFields = document.getElementById('studentFields');
                        if (studentFields) {
                            studentFields.style.display = 'block';
                            // Make institution required for students
                            const institutionField = document.getElementById('institution');
                            if (institutionField) {
                                institutionField.required = true;
                            }
                            // Make student ID file required for students
                            const studentIdFile = document.getElementById('student_id_file');
                            if (studentIdFile) {
                                studentIdFile.required = true;
                            }
                        }
                        
                        // Hide delegate fields
                        const delegateFields = document.getElementById('delegateFields');
                        if (delegateFields) {
                            delegateFields.style.display = 'none';
                            const delegateCategoryField = document.getElementById('delegate_category');
                            if (delegateCategoryField) {
                                delegateCategoryField.required = false;
                            }
                        }
                        
                        // Hide airport fields
                        const airportFields = document.getElementById('airportFields');
                        if (airportFields) {
                            airportFields.style.display = 'none';
                            const airportField = document.getElementById('airport_of_origin');
                            if (airportField) {
                                airportField.required = false;
                            }
                        }
                        
                        // Make passport file optional for non-delegates
                        const passportFileField = document.getElementById('passport_file');
                        if (passportFileField) {
                            passportFileField.required = false;
                        }
                        
                        // Update participant fields
                        updateParticipantFields();
                    } else if (selectedPackage.name.toLowerCase() === 'delegates') {
                        // Show organization fields for delegates
                        const organizationFields = document.getElementById('organizationFields');
                        if (organizationFields) {
                            organizationFields.style.display = 'block';
                        }
                        
                        // Reset organization and position fields
                        const organizationField = document.getElementById('organization');
                        const positionField = document.getElementById('position');
                        if (organizationField) {
                            organizationField.value = '';
                            organizationField.required = true;
                        }
                        if (positionField) {
                            positionField.value = '';
                        }
                        
                        // Hide student fields
                        const studentFields = document.getElementById('studentFields');
                        if (studentFields) {
                            studentFields.style.display = 'none';
                            const institutionField = document.getElementById('institution');
                            if (institutionField) {
                                institutionField.required = false;
                            }
                            // Remove student ID file requirement
                            const studentIdFile = document.getElementById('student_id_file');
                            if (studentIdFile) {
                                studentIdFile.required = false;
                            }
                        }
                        
                        // Show delegate fields
                        const delegateFields = document.getElementById('delegateFields');
                        if (delegateFields) {
                            delegateFields.style.display = 'block';
                            const delegateCategoryField = document.getElementById('delegate_category');
                            if (delegateCategoryField) {
                                delegateCategoryField.required = true;
                            }
                        }
                        
                        // Show airport fields
                        const airportFields = document.getElementById('airportFields');
                        if (airportFields) {
                            airportFields.style.display = 'block';
                            const airportField = document.getElementById('airport_of_origin');
                            if (airportField) {
                                airportField.required = true;
                            }
                        }
                        
                        // Make passport file required for delegates
                        const passportFileField = document.getElementById('passport_file');
                        if (passportFileField) {
                            passportFileField.required = true;
                        }
                        
                        // Update participant fields
                        updateParticipantFields();
                    } else {
                        // Show organization fields for non-students
                        const organizationFields = document.getElementById('organizationFields');
                        if (organizationFields) {
                            organizationFields.style.display = 'block';
                        }
                        
                        // Reset organization and position fields
                        const organizationField = document.getElementById('organization');
                        const positionField = document.getElementById('position');
                        if (organizationField) {
                            organizationField.value = '';
                            organizationField.required = true;
                        }
                        if (positionField) {
                            positionField.value = '';
                        }
                        
                        // Hide student fields for other packages
                        const studentFields = document.getElementById('studentFields');
                        if (studentFields) {
                            studentFields.style.display = 'none';
                            // Make institution not required for non-students
                            const institutionField = document.getElementById('institution');
                            if (institutionField) {
                                institutionField.required = false;
                            }
                            // Remove student ID file requirement
                            const studentIdFile = document.getElementById('student_id_file');
                            if (studentIdFile) {
                                studentIdFile.required = false;
                            }
                        }
                        // Update participant student fields
                        updateParticipantStudentFields();
                    }
                } else {
                    // Other individual packages - enable both individual and group options
                    console.log('Regular individual package selected - enabling both individual and group registration');
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = false;
                groupRadio.checked = false;
                    
                    // Reset group label styling
                    const groupLabel = document.querySelector('label[for="group"]');
                    if (groupLabel) {
                        groupLabel.style.opacity = '1';
                        groupLabel.style.cursor = 'pointer';
                        groupLabel.title = '';
                    }
                    
                    // Show organization fields for non-students
                    const organizationFields = document.getElementById('organizationFields');
                    if (organizationFields) {
                        organizationFields.style.display = 'block';
                    }
                    
                    // Reset organization and position fields
                    const organizationField = document.getElementById('organization');
                    const positionField = document.getElementById('position');
                    if (organizationField) {
                        organizationField.value = '';
                        organizationField.required = true;
                    }
                    if (positionField) {
                        positionField.value = '';
                    }
                    
                        // Hide student fields for non-student packages
                        const studentFields = document.getElementById('studentFields');
                        if (studentFields) {
                            studentFields.style.display = 'none';
                            // Make institution not required for non-students
                            const institutionField = document.getElementById('institution');
                            if (institutionField) {
                                institutionField.required = false;
                            }
                            // Remove student ID file requirement
                            const studentIdFile = document.getElementById('student_id_file');
                            if (studentIdFile) {
                                studentIdFile.required = false;
                            }
                        }
                        
                        // Hide delegate fields for non-delegate packages
                        const delegateFields = document.getElementById('delegateFields');
                        if (delegateFields) {
                            delegateFields.style.display = 'none';
                            const delegateCategoryField = document.getElementById('delegate_category');
                            if (delegateCategoryField) {
                                delegateCategoryField.required = false;
                            }
                        }
                        
                        // Hide airport fields for non-delegate packages
                        const airportFields = document.getElementById('airportFields');
                        if (airportFields) {
                            airportFields.style.display = 'none';
                            const airportField = document.getElementById('airport_of_origin');
                            if (airportField) {
                                airportField.required = false;
                            }
                        }
                        
                        // Make passport file optional for non-delegates
                        const passportFileField = document.getElementById('passport_file');
                        if (passportFileField) {
                            passportFileField.required = false;
                        }
                        
                        // Update participant fields
                        updateParticipantFields();
                }
            } else if (selectedPackage.type === 'side_event') {
                // Side event package - only allow individual registration
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = true;
                groupRadio.checked = false;
                
                // Reset group label styling
                const groupLabel = document.querySelector('label[for="group"]');
                if (groupLabel) {
                    groupLabel.style.opacity = '1';
                    groupLabel.style.cursor = 'not-allowed';
                    groupLabel.title = 'Group registration is not available for side event packages';
                }
                
                // Show organization fields for side events
                const organizationFields = document.getElementById('organizationFields');
                if (organizationFields) {
                    organizationFields.style.display = 'block';
                }
                
                // Reset organization and position fields
                const organizationField = document.getElementById('organization');
                const positionField = document.getElementById('position');
                if (organizationField) {
                    organizationField.value = '';
                    organizationField.required = true;
                }
                if (positionField) {
                    positionField.value = '';
                }
                
                // Hide student fields for side events
                const studentFields = document.getElementById('studentFields');
                if (studentFields) {
                    studentFields.style.display = 'none';
                    const institutionField = document.getElementById('institution');
                    if (institutionField) {
                        institutionField.required = false;
                    }
                }
                
                // Hide delegate fields for side events
                const delegateFields = document.getElementById('delegateFields');
                if (delegateFields) {
                    delegateFields.style.display = 'none';
                    const delegateCategoryField = document.getElementById('delegate_category');
                    if (delegateCategoryField) {
                        delegateCategoryField.required = false;
                    }
                }
                
                // Hide airport fields for side events
                const airportFields = document.getElementById('airportFields');
                if (airportFields) {
                    airportFields.style.display = 'none';
                    const airportField = document.getElementById('airport_of_origin');
                    if (airportField) {
                        airportField.required = false;
                    }
                }
                
                // Make passport file optional for side events
                const passportFileField = document.getElementById('passport_file');
                if (passportFileField) {
                    passportFileField.required = false;
                }
                
                // Update participant fields
                updateParticipantFields();
            } else if (selectedPackage.type === 'exhibition') {
                // Exhibition package - only allow individual registration
                individualRadio.checked = true;
                individualRadio.disabled = false;
                groupRadio.disabled = true;
                groupRadio.checked = false;
                
                // Reset group label styling
                const groupLabel = document.querySelector('label[for="group"]');
                if (groupLabel) {
                    groupLabel.style.opacity = '1';
                    groupLabel.style.cursor = 'not-allowed';
                    groupLabel.title = 'Group registration is not available for exhibition packages';
                }
                
                // Show organization fields for exhibitions
                const organizationFields = document.getElementById('organizationFields');
                if (organizationFields) {
                    organizationFields.style.display = 'block';
                }
                
                // Reset organization and position fields
                const organizationField = document.getElementById('organization');
                const positionField = document.getElementById('position');
                if (organizationField) {
                    organizationField.value = '';
                    organizationField.required = true;
                }
                if (positionField) {
                    positionField.value = '';
                }
                
                // Hide student fields for exhibitions
                const studentFields = document.getElementById('studentFields');
                if (studentFields) {
                    studentFields.style.display = 'none';
                    const institutionField = document.getElementById('institution');
                    if (institutionField) {
                        institutionField.required = false;
                    }
                }
                
                // Hide delegate fields for exhibitions
                const delegateFields = document.getElementById('delegateFields');
                if (delegateFields) {
                    delegateFields.style.display = 'none';
                    const delegateCategoryField = document.getElementById('delegate_category');
                    if (delegateCategoryField) {
                        delegateCategoryField.required = false;
                    }
                }
                
                // Hide airport fields for exhibitions
                const airportFields = document.getElementById('airportFields');
                if (airportFields) {
                    airportFields.style.display = 'none';
                    const airportField = document.getElementById('airport_of_origin');
                    if (airportField) {
                        airportField.required = false;
                    }
                }
                
                // Make passport file optional for exhibitions
                const passportFileField = document.getElementById('passport_file');
                if (passportFileField) {
                    passportFileField.required = false;
                }
                
                // Update participant fields
                updateParticipantFields();
            }
            
            // Trigger registration type change
            const event = new Event('change');
            if (individualRadio.checked) {
                individualRadio.dispatchEvent(event);
            } else if (groupRadio.checked) {
                groupRadio.dispatchEvent(event);
            }
            
            // Fetch filtered countries and nationalities based on continent policy and repopulate selects
            (function() {
                const policy = (selectedPackage && selectedPackage.continent) ? selectedPackage.continent : 'all';
                const url = new URL('api/get_countries.php', window.location.href);
                url.searchParams.set('continent_policy', policy);
                url.searchParams.set('include_nationalities', 'true');
                console.log('Fetching filtered lists with policy:', policy, url.toString());
                
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.error || 'Failed to fetch filtered lists');
                        console.log('Filtered lists received:', {
                            policy,
                            countries: (data.countries || []).length,
                            nationalities: (data.nationalities || []).length
                        });
                        const $nat = $('#nationality');
                        const $country = $('#country');
                        // Ensure Select2 is destroyed before DOM mutations
                        if ($nat.hasClass('select2-hidden-accessible')) { $nat.select2('destroy'); }
                        if ($country.hasClass('select2-hidden-accessible')) { $country.select2('destroy'); }
                        // Repopulate nationality select
                        const nationalityValue = $nat.val() || '';
                        $nat.empty().append('<option value="">Select Nationality</option>');
                        (data.nationalities || []).forEach(n => {
                            const sel = nationalityValue && nationalityValue === n.nationality ? ' selected' : '';
                            $nat.append(`<option value="${n.nationality}" data-continent="${n.continent}"${sel}>${n.country_name} (${n.nationality})</option>`);
                        });
                        // Repopulate country select
                        const countryValue = $country.val() || '';
                        $country.empty().append('<option value="">Select Country</option>');
                        (data.countries || []).forEach(c => {
                            const sel = countryValue && countryValue === c.name ? ' selected' : '';
                            $country.append(`<option value="${c.name}" data-continent="${c.continent}"${sel}>${c.name}</option>`);
                        });
                        // Re-init Select2 directly to avoid double-binding
                        $nat.select2({ theme: 'bootstrap-5', placeholder: 'Select Nationality', allowClear: true, width: '100%' });
                        $country.select2({ theme: 'bootstrap-5', placeholder: 'Select Country', allowClear: true, width: '100%' });
                        // Sync participant nationality selects with main
                        syncParticipantNationalityOptions();
                    })
                    .catch(err => {
                        console.error('Filtered lists fetch error:', err);
                        // Fallback (kept for resilience)
                populateNationalitySelect();
                    });
            })();
        } catch (error) {
            console.error('Error in selectPackage:', error);
        }
    }

    // Fetch filtered countries/nationalities by continent policy and repopulate selects
    function fetchFilteredListsAndRepopulate() {
        const policy = (selectedPackage && selectedPackage.continent) ? selectedPackage.continent : 'all';
        const url = new URL('api/get_countries.php', window.location.href);
        url.searchParams.set('continent_policy', policy);
        url.searchParams.set('include_nationalities', 'true');
        
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.error || 'Failed to fetch filtered lists');
                // Repopulate nationality
                const nationalityValue = $('#nationality').val() || '';
                $('#nationality').empty().append('<option value="">Select Nationality</option>');
                (data.nationalities || []).forEach(n => {
                    const isSelected = nationalityValue && nationalityValue === n.nationality ? ' selected' : '';
                    $('#nationality').append(`<option value="${n.nationality}" data-continent="${n.continent}"${isSelected}>${n.country_name} (${n.nationality})</option>`);
                });
                // Repopulate country
                const countryValue = $('#country').val() || '';
                $('#country').empty().append('<option value="">Select Country</option>');
                (data.countries || []).forEach(c => {
                    const isSelected = countryValue && countryValue === c.name ? ' selected' : '';
                    $('#country').append(`<option value="${c.name}" data-continent="${c.continent}"${isSelected}>${c.name}</option>`);
                });
                // Re-init Select2
                initializeSelect2();
                // Sync participant nationality selects with main
                syncParticipantNationalityOptions();
            })
            .catch(err => {
                console.error('Filtered lists fetch error:', err);
                // Fallback to existing populate behavior
                populateNationalitySelect();
            });
    }

    function syncParticipantNationalityOptions() {
        const mainNationalitySelect = document.getElementById('nationality');
        const participantSelects = document.querySelectorAll('.participant-nationality');
        participantSelects.forEach(nationalitySelect => {
            const current = nationalitySelect.value;
            nationalitySelect.innerHTML = '<option value="">Select Nationality</option>';
            Array.from(mainNationalitySelect.options).slice(1).forEach(option => {
                const newOption = option.cloneNode(true);
                if (option.hasAttribute('data-continent')) {
                    newOption.setAttribute('data-continent', option.getAttribute('data-continent'));
                }
                nationalitySelect.appendChild(newOption);
            });
            // restore value if still present
            if (current) {
                const exists = Array.from(nationalitySelect.options).some(o => o.value === current && o.style.display !== 'none');
                nationalitySelect.value = exists ? current : '';
            }
            $(nationalitySelect).select2('destroy').select2({ theme: 'bootstrap-5', placeholder: 'Select Nationality', allowClear: true, width: '100%' });
            updateParticipantNationalityFilter(nationalitySelect);
        });
    }

    function updateSelectedPackageInfo() {
        if (selectedPackage) {
            const priceDisplay = selectedPackage.price > 0 ? `<div class="package-price">$${selectedPackage.price.toLocaleString()}</div>` : '';
            const iconDisplay = selectedPackage.icon ? `<div class="package-icon mb-2"><i class="${selectedPackage.icon} ${selectedPackage.color || 'text-primary'} fa-2x"></i></div>` : '';
            selectedPackageInfo.innerHTML = `
                <div class="selected-package-card">
                    ${iconDisplay}
                    <h4>${selectedPackage.name}</h4>
                    ${priceDisplay}
                    <div class="package-description mt-3" id="packageDescription" style="display: none;">
                        <small class="text-muted" id="packageDescriptionText">Loading description...</small>
                    </div>
                </div>
            `;
        }
    }

    function showPackageDescription() {
        if (!selectedPackage) return;
        
        // Get package description from the database via AJAX
        fetch('get_package_description.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                package_id: selectedPackage.id
            })
        })
        .then(response => response.json())
        .then(data => {
            const packageDescription = document.getElementById('packageDescription');
            const packageDescriptionText = document.getElementById('packageDescriptionText');
            
            if (packageDescription && packageDescriptionText) {
                if (data.success && data.description) {
                    // Update the description text
                    packageDescriptionText.textContent = data.description;
                    // Show the description
                    packageDescription.style.display = 'block';
                } else {
                    // Hide the description if no description available
                    packageDescription.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching package description:', error);
            // Hide the description on error
            const packageDescription = document.getElementById('packageDescription');
            if (packageDescription) {
                packageDescription.style.display = 'none';
            }
        });
    }

    function loadCountries() {
        return fetch('api/get_countries.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countries = data.countries;
                    console.log('Countries loaded:', countries.length);
                    // Only populate if no package is selected yet
                    if (!selectedPackage) {
                populateNationalitySelect();
                    }
                    return data.countries;
                } else {
                    throw new Error(data.error || 'Failed to load countries');
                }
            })
            .catch(error => {
                console.error('Error loading countries:', error);
                // Fallback to basic options
                nationalitySelect.innerHTML = `
                    <option value="">Select Nationality</option>
                    <option value="Ghanaian">Ghana (Ghanaian)</option>
                    <option value="Nigerian">Nigeria (Nigerian)</option>
                    <option value="South African">South Africa (South African)</option>
                    <option value="Kenyan">Kenya (Kenyan)</option>
                    <option value="Other">Other (Other)</option>
                `;
                throw error;
            });
    }

    function loadNationalities(packageId = null, packageName = null) {
        const url = new URL('api/get_countries.php', window.location.href);
        url.searchParams.set('nationalities_only', 'true');
        if (packageId) url.searchParams.set('package_id', packageId);
        if (packageName) url.searchParams.set('package_name', packageName);
        
        return fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Nationalities loaded:', data.nationalities.length, 'for package:', packageName);
                    // Store nationalities globally for reuse
                    if (!packageId && !packageName) {
                        nationalities = data.nationalities;
                    }
                    return data.nationalities;
                } else {
                    throw new Error(data.error || 'Failed to load nationalities');
                }
            })
            .catch(error => {
                console.error('Error loading nationalities:', error);
                // Fallback to basic options
                return [
                    { nationality: 'Ghanaian', country_name: 'Ghana', country_code: 'GH' },
                    { nationality: 'Nigerian', country_name: 'Nigeria', country_code: 'NG' },
                    { nationality: 'South African', country_name: 'South Africa', country_code: 'ZA' },
                    { nationality: 'Kenyan', country_name: 'Kenya', country_code: 'KE' }
                ];
            });
    }

    function populateNationalitySelect() {
        console.log('populateNationalitySelect called');
        console.log('selectedPackage:', selectedPackage);
        
        // Get all nationality options (excluding the first "Select Nationality" option)
        const allOptions = Array.from(nationalitySelect.options).slice(1);
        
        if (!selectedPackage) {
            // No package selected - show all nationalities
            console.log('No package selected, showing all nationalities');
            allOptions.forEach(option => {
                option.style.display = '';
            });
        } else {
            // Package selected - filter based on package continent policy
            const packageContinentPolicy = (selectedPackage.continent || 'all').toString().toLowerCase();
            const packageName = selectedPackage.name ? selectedPackage.name.toLowerCase() : '';
            console.log('Filtering nationalities for package continent policy:', packageContinentPolicy);
            
            let visibleCount = 0;
            let hiddenCount = 0;
            
            allOptions.forEach(option => {
                const nationality = option.value;
                let shouldShow = true;
                
                if (packageContinentPolicy === 'africa') {
                    // Only African nationalities
                    shouldShow = isAfricanByContinent(option);
                } else if (packageContinentPolicy === 'other') {
                    // Exclude African nationalities
                    shouldShow = !isAfricanByContinent(option);
                } else if (packageContinentPolicy === 'all') {
                    shouldShow = true;
                } else {
                    // Fallback to legacy name-based behavior if policy not set
                if (packageName.includes('african nationals') && !packageName.includes('non')) {
                    shouldShow = isAfricanByContinent(option);
                } else if (packageName.includes('non') && packageName.includes('african nationals')) {
                    shouldShow = !isAfricanByContinent(option);
                } else {
                        shouldShow = true;
                }
                }
                
                if (shouldShow) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                    hiddenCount++;
                }
            });
            
            console.log('Filtering complete - Visible:', visibleCount, 'Hidden:', hiddenCount);
        }
        
        // Reinitialize Select2 to reflect changes
        initializeSelect2();

        // Also filter country select by package continent policy
        filterCountrySelectByPackagePolicy();
    }

    // Initialize Select2 for nationality and country dropdowns
    function initializeSelect2() {
        // Destroy existing Select2 if it exists
        if ($('#nationality').hasClass('select2-hidden-accessible')) {
            $('#nationality').select2('destroy');
        }
        
        $('#nationality').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Nationality',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for country dropdown
        if ($('#country').hasClass('select2-hidden-accessible')) {
            $('#country').select2('destroy');
        }
        
        $('#country').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Country',
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

    // Filter country dropdown based on selectedPackage.continent policy
    function filterCountrySelectByPackagePolicy() {
        const countrySelect = document.getElementById('country');
        if (!countrySelect) return;
        const options = Array.from(countrySelect.options).slice(1);
        if (!selectedPackage) {
            options.forEach(o => { o.style.display = ''; });
            $('#country').trigger('change.select2');
            return;
        }
        const policy = (selectedPackage.continent || 'all').toString().toLowerCase();
        options.forEach(option => {
            const continent = option.getAttribute('data-continent');
            let show = true;
            if (policy === 'africa') {
                show = continent === 'Africa';
            } else if (policy === 'other') {
                show = continent !== 'Africa';
            } else {
                show = true;
            }
            option.style.display = show ? '' : 'none';
        });
        // If current value is hidden by filter, reset selection
        const selectedOption = countrySelect.selectedOptions[0];
        if (selectedOption && selectedOption.style.display === 'none') {
            countrySelect.value = '';
        }
        $('#country').trigger('change.select2');
    }

    // Restore package selection from form data
    function restorePackageSelection() {
        const packageId = selectedPackageId.value;
        if (packageId) {
            console.log('Restoring package selection for ID:', packageId);
            
            // Find the package card with the matching ID
            const packageCard = document.querySelector(`[data-package-id="${packageId}"]`);
            if (packageCard) {
                console.log('Found package card:', packageCard);
                
                // Simulate a click on the package card to restore selection
                packageCard.click();
                
                // Also restore the form data if it exists
                restoreFormData();
            } else {
                console.log('Package card not found for ID:', packageId);
            }
        } else {
            console.log('No package ID to restore');
        }
    }
    
    // Restore form data from PHP
    function restoreFormData() {
        // This will be called after package selection is restored
        console.log('Form data restoration will be handled by existing form restoration logic');
    }

    // Initialize email validation
    function initializeEmailValidation() {
        const emailInput = document.getElementById('email');
        if (emailInput) {
            // Real-time validation as user types
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const sanitized = validateAndSanitizeEmail(email);
                
                // Update the input with sanitized version
                if (email !== sanitized) {
                    this.value = sanitized;
                }
                
                // Visual feedback for validation
                if (email && !isValidEmail(sanitized)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (email && isValidEmail(sanitized)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
            
            // Validate on blur
            emailInput.addEventListener('blur', function() {
                const email = this.value;
                if (email && !isValidEmail(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (email && isValidEmail(email)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    
    // Initialize field requirements based on current state
    function initializeFieldRequirements() {
        // Set delegate_category as not required by default
        const delegateCategoryField = document.getElementById('delegate_category');
        if (delegateCategoryField) {
            delegateCategoryField.required = false;
        }
        
        // Set airport_of_origin as not required by default
        const airportField = document.getElementById('airport_of_origin');
        if (airportField) {
            airportField.required = false;
        }
        
        // Set student_id_file as not required by default
        const studentIdFile = document.getElementById('student_id_file');
        if (studentIdFile) {
            studentIdFile.required = false;
        }
        
        // Hide delegate fields by default
        const delegateFields = document.getElementById('delegateFields');
        if (delegateFields) {
            delegateFields.style.display = 'none';
        }
        
        // Hide airport fields by default
        const airportFields = document.getElementById('airportFields');
        if (airportFields) {
            airportFields.style.display = 'none';
        }
        
        // Hide student fields by default
        const studentFields = document.getElementById('studentFields');
        if (studentFields) {
            studentFields.style.display = 'none';
        }
    }
    
    // Update participant field requirements based on selected package
    function updateParticipantFieldRequirements(participantDiv) {
        if (!selectedPackage) return;
        
        const packageName = selectedPackage.name ? selectedPackage.name.toLowerCase() : '';
        
        // Get participant fields
        const studentIdFile = participantDiv.querySelector('input[name*="[student_id_file]"]');
        const delegateCategoryField = participantDiv.querySelector('select[name*="[delegate_category]"]');
        const airportField = participantDiv.querySelector('input[name*="[airport_of_origin]"]');
        const institutionField = participantDiv.querySelector('input[name*="[institution]"]');
        
        // Set student_id_file requirement
        if (studentIdFile) {
            studentIdFile.required = packageName === 'students';
        }
        
        // Set delegate_category requirement
        if (delegateCategoryField) {
            delegateCategoryField.required = packageName === 'delegates';
        }
        
        // Set airport_of_origin requirement
        if (airportField) {
            airportField.required = packageName === 'delegates';
        }
        
        // Set institution requirement
        if (institutionField) {
            institutionField.required = packageName === 'students';
        }
    }

    // Function to check if nationality is African by continent
    function isAfricanByContinent(option) {
        const continent = option.getAttribute('data-continent');
        console.log('Checking continent for option:', option.value, 'continent:', continent);
        return continent === 'Africa';
    }

    // Function to check if nationality is African (legacy function)
    function isAfricanNational(nationality) {
        console.log('Checking if nationality is African:', nationality);
        console.log('African nationalities loaded:', africanNationalities.length);
        
        // Use the African nationalities loaded from database
        if (africanNationalities && africanNationalities.length > 0) {
            const isAfrican = africanNationalities.includes(nationality);
            console.log('Database check result:', isAfrican);
            return isAfrican;
        }
        
        // Fallback to hardcoded list if database data not loaded
        console.log('Using fallback African nationalities list');
        const fallbackAfricanNationalities = [
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
        
        const isAfrican = fallbackAfricanNationalities.includes(nationality);
        console.log('Fallback check result:', isAfrican);
        return isAfrican;
    }

    // Function to load countries filtered by package type
    function loadCountriesByPackage(packageId, packageName) {
        const url = new URL('api/get_countries.php', window.location.href);
        if (packageId) url.searchParams.set('package_id', packageId);
        if (packageName) url.searchParams.set('package_name', packageName);
        
        return fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Filtered countries loaded:', data.countries.length, 'for package:', packageName);
                    return data.countries;
                } else {
                    throw new Error(data.error || 'Failed to load filtered countries');
                }
            })
            .catch(error => {
                console.error('Error loading filtered countries:', error);
                return countries; // Fallback to all countries
            });
    }

    // Legacy function for backward compatibility
    function filterCountriesByPackage(packageId, packageName) {
        if (!countries || countries.length === 0) return countries;
        
        let filteredCountries = countries;
        
        console.log('Filtering countries for package:', packageName);
        console.log('Package name type:', typeof packageName);
        console.log('Package name length:', packageName ? packageName.length : 'null');
        
        // Check package name for African/Non-African filtering
        const normalizedPackageName = packageName ? packageName.toLowerCase().trim() : '';
        console.log('Normalized package name:', normalizedPackageName);
        
        if (normalizedPackageName.includes('african nationals') && !normalizedPackageName.includes('non')) {
            // African Nationals package - show only African countries
            filteredCountries = countries.filter(country => isAfricanNational(country.nationality));
            console.log('African Nationals package - showing', filteredCountries.length, 'African countries');
        } else if (normalizedPackageName.includes('non') && normalizedPackageName.includes('african nationals')) {
            // Non-African Nationals package - show only non-African countries
            filteredCountries = countries.filter(country => !isAfricanNational(country.nationality));
            console.log('Non-African Nationals package - showing', filteredCountries.length, 'non-African countries');
        } else {
            // Students, Delegates, Side events, Exhibitions - show all countries
            console.log('Other package (' + packageName + ') - showing all', countries.length, 'countries');
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
            // Pricing is handled automatically based on nationality
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
        
        // Pricing is handled automatically based on participant nationalities
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
                // Auto-add participant forms based on number of people
                const numPeople = parseInt(numPeopleInput.value) || 0;
                if (numPeople > 0) {
                    updateParticipants(numPeople);
                } else {
                    // Add initial participant form if no number specified
                if (participantCount === 0) {
                    addParticipantForm();
                    }
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
            
            // Auto-check the "Add participants now" checkbox if number > 0
            if (numPeople > 0 && addParticipantsNowCheckbox) {
                addParticipantsNowCheckbox.checked = true;
                participantsDetails.style.display = 'block';
            }
            
            updateSummary();
        });
    }

    // Add participant button (removed - now controlled automatically)
    // if (addParticipantBtn) {
    //     addParticipantBtn.addEventListener('click', function() {
    //         addParticipantForm();
    //     });
    // }

    // Email validation and sanitization function
    function validateAndSanitizeEmail(email) {
        if (!email) return '';
        
        // Strip unnecessary characters and normalize
        let sanitized = email.trim().toLowerCase();
        
        // Remove any non-printable characters
        sanitized = sanitized.replace(/[^\x20-\x7E]/g, '');
        
        // Remove extra spaces
        sanitized = sanitized.replace(/\s+/g, '');
        
        return sanitized;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        // Validate and sanitize email
        const emailInput = document.getElementById('email');
        if (emailInput) {
            const originalEmail = emailInput.value;
            const sanitizedEmail = validateAndSanitizeEmail(originalEmail);
            
            if (!sanitizedEmail) {
                e.preventDefault();
                showError('Please enter a valid email address', 'Email Required');
                return;
            }
            
            if (!isValidEmail(sanitizedEmail)) {
                e.preventDefault();
                showError('Please enter a valid email address format', 'Invalid Email');
                return;
            }
            
            // Update the input with sanitized email
            emailInput.value = sanitizedEmail;
        }
        
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
        
        // Check if this is a fixed-price package (Students, Delegates, Side Events, Exhibitions)
        const isFixedPricePackage = selectedPackage && (
            selectedPackage.name.toLowerCase() === 'students' || 
            selectedPackage.name.toLowerCase() === 'delegates' ||
            selectedPackage.type === 'side_event' || 
            selectedPackage.type === 'exhibition'
        );
        
        if (isFixedPricePackage) {
            // For fixed-price packages, use exact package price
            const totalCost = selectedPackage.price;
            let packageType = 'Fixed price';
            if (selectedPackage.type === 'side_event') packageType = 'Side Event';
            else if (selectedPackage.type === 'exhibition') packageType = 'Exhibition';
            else if (selectedPackage.name.toLowerCase() === 'students') packageType = 'Student';
            else if (selectedPackage.name.toLowerCase() === 'delegates') packageType = 'Delegate';
            
            costEstimation.innerHTML = `
                <strong>Total: $${totalCost.toLocaleString()}</strong>
                <br><small class="text-muted">${packageType} package price</small>
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
        
        // Add the main registrant to the count
        const mainNationality = $('#nationality').val();
        if (mainNationality) {
            if (isAfricanNational(mainNationality)) {
                africanCount++;
            } else {
                nonAfricanCount++;
            }
        }
        
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
        
        // Subtract 1 because the focal person is already captured in the main form
        const participantsNeeded = Math.max(0, numPeople - 1);
        const currentCount = participantsContainer.querySelectorAll('.participant-card').length;
        
        if (participantsNeeded > currentCount) {
            // Add participants
            const formsToAdd = participantsNeeded - currentCount;
            for (let i = 0; i < formsToAdd; i++) {
                addParticipantForm();
            }
        } else if (participantsNeeded < currentCount) {
            // Remove participants
            const participants = participantsContainer.querySelectorAll('.participant-card');
            for (let i = currentCount - 1; i >= participantsNeeded; i--) {
                participants[i].remove();
            }
            participantCount = Math.min(participantCount, participantsNeeded);
        }
    }

    function addParticipantForm() {
        participantCount++;
        const participantDiv = document.createElement('div');
        participantDiv.className = 'card mb-3 participant-card';
        participantDiv.innerHTML = `
            <div class="card-header">
                <h6 class="mb-0">Additional Participant ${participantCount}</h6>
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
                        <label class="form-label">Passport Copy (PDF) *</label>
                        <input type="file" class="form-control" name="participants[${participantCount - 1}][passport_file]" accept=".pdf">
                        <div class="form-text small">Required for delegate participants - upload passport copy (PDF, max 5MB)</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Requires visa to enter South Africa? *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="participants[${participantCount - 1}][requires_visa]" value="1">
                            <label class="form-check-label">
                                Yes
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="participants[${participantCount - 1}][requires_visa]" value="0">
                            <label class="form-check-label">
                                No
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nationality *</label>
                        <select name="participants[${participantCount - 1}][nationality]" class="form-select participant-nationality" required>
                            <option value="">Select Nationality</option>
                        </select>
                    </div>
                </div>
                <div class="row participant-organization-fields">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Organization *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][organization]" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Position/Title</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][position]">
                    </div>
                </div>
                <div class="row participant-student-fields" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Institution/School *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][institution]" placeholder="Enter institution or school name" required>
                        <div class="form-text small">Required for student participants</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student ID Document *</label>
                        <input type="file" class="form-control" name="participants[${participantCount - 1}][student_id_file]" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text small">Upload student ID (PDF, JPG, PNG - max 5MB)</div>
                    </div>
                </div>
                <div class="row participant-delegate-fields" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <!-- Empty left column for delegates -->
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delegate Category *</label>
                        <select class="form-select" name="participants[${participantCount - 1}][delegate_category]" required>
                            <option value="">Select Category</option>
                            <option value="Oral abstract presenter">Oral abstract presenter</option>
                            <option value="Invited speaker/Moderator">Invited speaker/Moderator</option>
                            <option value="Scientific Program Committee Member">Scientific Program Committee Member</option>
                            <option value="Secretariat">Secretariat</option>
                            <option value="Media Partner">Media Partner</option>
                            <option value="Side event focal person">Side event focal person</option>
                        </select>
                        <div class="form-text small">Required for delegate participants</div>
                    </div>
                </div>
                <div class="row participant-airport-fields" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <!-- Empty left column for delegates -->
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Airport of Origin *</label>
                        <input type="text" class="form-control" name="participants[${participantCount - 1}][airport_of_origin]" placeholder="Enter departure airport">
                        <div class="form-text small">Required for delegate participants - for travel planning purposes</div>
                    </div>
                </div>
            </div>
        `;
        
        participantsContainer.appendChild(participantDiv);
        
        // Set field requirements based on selected package
        updateParticipantFieldRequirements(participantDiv);
        
        // Add email validation for participant
        const participantEmailInput = participantDiv.querySelector('input[name*="[email]"]');
        if (participantEmailInput) {
            participantEmailInput.addEventListener('input', function() {
                const email = this.value;
                const sanitized = validateAndSanitizeEmail(email);
                
                // Update the input with sanitized version
                if (email !== sanitized) {
                    this.value = sanitized;
                }
                
                // Visual feedback for validation
                if (email && !isValidEmail(sanitized)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (email && isValidEmail(sanitized)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
            
            // Validate on blur
            participantEmailInput.addEventListener('blur', function() {
                const email = this.value;
                if (email && !isValidEmail(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (email && isValidEmail(email)) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            });
        }
        
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
        
        // Populate nationality dropdown for this participant using the same options as main nationality
        const nationalitySelect = participantDiv.querySelector('.participant-nationality');
        const mainNationalitySelect = document.getElementById('nationality');
        
        // Copy all options from main nationality select (including data-continent attributes)
        nationalitySelect.innerHTML = '<option value="">Select Nationality</option>';
        Array.from(mainNationalitySelect.options).slice(1).forEach(option => {
            const newOption = option.cloneNode(true);
            // Ensure data-continent attribute is copied
            if (option.hasAttribute('data-continent')) {
                newOption.setAttribute('data-continent', option.getAttribute('data-continent'));
            }
            nationalitySelect.appendChild(newOption);
        });
        
        // Apply package-based filtering to participant nationality
        updateParticipantNationalityFilter(nationalitySelect);
        
        // Reinitialize Select2 after populating options
        $(nationalitySelect).select2('destroy').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Nationality',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            const registrationType = document.querySelector('input[name="registration_type"]:checked');
            if (registrationType && registrationType.value === 'group') {
                checkGroupPricing();
                const numPeople = parseInt(numPeopleInput.value) || 0;
                if (numPeople > 0) {
                    updateCostEstimation(numPeople);
                }
            }
        });
        
        // Show/hide fields for group participants based on selected package
        updateParticipantFields();
    }

    // removeParticipant function removed - participants are now automatically managed

    function updateParticipantNationalityFilter(nationalitySelect) {
        if (!nationalitySelect) return;
        
        const allOptions = Array.from(nationalitySelect.options).slice(1);
        
        if (!selectedPackage) {
            // No package selected - show all nationalities
            allOptions.forEach(option => {
                option.style.display = '';
            });
        } else {
            // Package selected - filter based on package continent policy
            const packageContinentPolicy = (selectedPackage.continent || 'all').toString().toLowerCase();
            const packageName = selectedPackage.name ? selectedPackage.name.toLowerCase() : '';
            
            allOptions.forEach(option => {
                const nationality = option.value;
                let shouldShow = true;
                
                if (packageContinentPolicy === 'africa') {
                    // Show only African nationalities - filter by continent
                    shouldShow = isAfricanByContinent(option);
                } else if (packageContinentPolicy === 'other') {
                    // Show only non-African nationalities - filter by continent
                    shouldShow = !isAfricanByContinent(option);
                } else if (packageContinentPolicy === 'all') {
                    shouldShow = true;
                } else {
                    // Fallback to legacy name-based behavior if policy not set
                    if (packageName.includes('african nationals') && !packageName.includes('non')) {
                        shouldShow = isAfricanByContinent(option);
                    } else if (packageName.includes('non') && packageName.includes('african nationals')) {
                        shouldShow = !isAfricanByContinent(option);
                    } else {
                        shouldShow = true;
                    }
                }
                // For students, delegates, and other packages, show all nationalities
                
                if (shouldShow) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }
    }

    function updateParticipantFields() {
        // Show/hide fields for all participant forms based on selected package
        const participantStudentFields = document.querySelectorAll('.participant-student-fields');
        const participantDelegateFields = document.querySelectorAll('.participant-delegate-fields');
        const participantAirportFields = document.querySelectorAll('.participant-airport-fields');
        const participantOrganizationFields = document.querySelectorAll('.participant-organization-fields');
        
        if (selectedPackage && selectedPackage.name.toLowerCase() === 'students') {
            // Hide organization and delegate fields, show student fields
            participantOrganizationFields.forEach(field => {
                field.style.display = 'none';
                // Set default values for organization and position
                const organizationField = field.querySelector('input[name*="[organization]"]');
                const positionField = field.querySelector('input[name*="[position]"]');
                if (organizationField) {
                    organizationField.value = 'N/A';
                    organizationField.required = false;
                }
                if (positionField) {
                    positionField.value = 'N/A';
                }
            });
            
            participantDelegateFields.forEach(field => {
                field.style.display = 'none';
                const delegateCategoryField = field.querySelector('select[name*="[delegate_category]"]');
                if (delegateCategoryField) {
                    delegateCategoryField.required = false;
                }
            });
            
            participantAirportFields.forEach(field => {
                field.style.display = 'none';
            });
            
            participantStudentFields.forEach(field => {
                field.style.display = 'block';
                // Make institution required for student participants
                const institutionField = field.querySelector('input[name*="[institution]"]');
                if (institutionField) {
                    institutionField.required = true;
                }
                // Make student_id_file required for student participants
                const studentIdFile = field.querySelector('input[name*="[student_id_file]"]');
                if (studentIdFile) {
                    studentIdFile.required = true;
                }
            });
        } else if (selectedPackage && selectedPackage.name.toLowerCase() === 'delegates') {
            // Show organization and delegate fields, hide student fields
            participantOrganizationFields.forEach(field => {
                field.style.display = 'block';
                // Reset organization and position fields
                const organizationField = field.querySelector('input[name*="[organization]"]');
                const positionField = field.querySelector('input[name*="[position]"]');
                if (organizationField) {
                    organizationField.value = '';
                    organizationField.required = true;
                }
                if (positionField) {
                    positionField.value = '';
                }
            });
            
            participantStudentFields.forEach(field => {
                field.style.display = 'none';
                const institutionField = field.querySelector('input[name*="[institution]"]');
                if (institutionField) {
                    institutionField.required = false;
                }
                // Make student_id_file not required for delegate participants
                const studentIdFile = field.querySelector('input[name*="[student_id_file]"]');
                if (studentIdFile) {
                    studentIdFile.required = false;
                }
            });
            
            participantDelegateFields.forEach(field => {
                field.style.display = 'block';
                const delegateCategoryField = field.querySelector('select[name*="[delegate_category]"]');
                if (delegateCategoryField) {
                    delegateCategoryField.required = true;
                }
            });
            
            participantAirportFields.forEach(field => {
                field.style.display = 'block';
                const airportField = field.querySelector('input[name*="[airport_of_origin]"]');
                const airportLabel = field.querySelector('label');
                const airportHelpText = field.querySelector('.form-text');
                if (airportField) {
                    airportField.required = true;
                }
                if (airportLabel) {
                    airportLabel.innerHTML = 'Airport of Origin *';
                }
                if (airportHelpText) {
                    airportHelpText.textContent = 'Required for delegate participants - for travel planning purposes';
                }
            });
            
            // Make passport files required for delegate participants
            const participantPassportFields = document.querySelectorAll('input[name*="[passport_file]"]');
            participantPassportFields.forEach(field => {
                field.required = true;
                const passportLabel = field.closest('.row').querySelector('label');
                const passportHelpText = field.closest('.row').querySelector('.form-text');
                if (passportLabel) {
                    passportLabel.innerHTML = 'Passport Copy (PDF) *';
                }
                if (passportHelpText) {
                    passportHelpText.textContent = 'Required for delegate participants - upload passport copy (PDF, max 5MB)';
                }
            });
        } else {
            // Show organization fields, hide student and delegate fields
            participantOrganizationFields.forEach(field => {
                field.style.display = 'block';
                // Reset organization and position fields
                const organizationField = field.querySelector('input[name*="[organization]"]');
                const positionField = field.querySelector('input[name*="[position]"]');
                if (organizationField) {
                    organizationField.value = '';
                    organizationField.required = true;
                }
                if (positionField) {
                    positionField.value = '';
                }
            });
            
            participantStudentFields.forEach(field => {
                field.style.display = 'none';
                const institutionField = field.querySelector('input[name*="[institution]"]');
                if (institutionField) {
                    institutionField.required = false;
                }
                // Make student_id_file not required for non-student participants
                const studentIdFile = field.querySelector('input[name*="[student_id_file]"]');
                if (studentIdFile) {
                    studentIdFile.required = false;
                }
            });
            
            participantDelegateFields.forEach(field => {
                field.style.display = 'none';
                const delegateCategoryField = field.querySelector('select[name*="[delegate_category]"]');
                if (delegateCategoryField) {
                    delegateCategoryField.required = false;
                }
            });
            
            participantAirportFields.forEach(field => {
                field.style.display = 'none';
                const airportField = field.querySelector('input[name*="[airport_of_origin]"]');
                const airportLabel = field.querySelector('label');
                const airportHelpText = field.querySelector('.form-text');
                if (airportField) {
                    airportField.required = false;
                }
                if (airportLabel) {
                    airportLabel.innerHTML = 'Airport of Origin';
                }
                if (airportHelpText) {
                    airportHelpText.textContent = 'Optional - for travel planning purposes';
                }
            });
            
            // Make passport files optional for non-delegate participants
            const participantPassportFields = document.querySelectorAll('input[name*="[passport_file]"]');
            participantPassportFields.forEach(field => {
                field.required = false;
                const passportLabel = field.closest('.row').querySelector('label');
                const passportHelpText = field.closest('.row').querySelector('.form-text');
                if (passportLabel) {
                    passportLabel.innerHTML = 'Passport Copy (PDF)';
                }
                if (passportHelpText) {
                    passportHelpText.textContent = 'Optional - upload passport copy (PDF, max 5MB)';
                }
            });
        }
        
        // Update nationality filtering for all participant nationality dropdowns
        const participantNationalitySelects = document.querySelectorAll('.participant-nationality');
        participantNationalitySelects.forEach(select => {
            updateParticipantNationalityFilter(select);
        });
    }

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
        
        // Determine actual pricing based on package type and nationality
        let actualPackage = selectedPackage;
        let pricingNote = '';
        
        // Check if this is a fixed-price package (Students, Delegates, Side Events, Exhibitions)
        const isFixedPricePackage = selectedPackage && (
            selectedPackage.name.toLowerCase() === 'students' || 
            selectedPackage.name.toLowerCase() === 'delegates' ||
            selectedPackage.type === 'side_event' || 
            selectedPackage.type === 'exhibition'
        );
        
        if (isFixedPricePackage) {
            // Fixed-price packages - use exact package price, not nationality-based
            actualPackage = selectedPackage;
            pricingNote = ' (Fixed price)';
        } else if (registrationType.value === 'individual') {
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
        if (isFixedPricePackage) {
            // For fixed-price packages (Students, Delegates, Side Events, Exhibitions), use exact package price
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
            if (formData.title) document.getElementById('title').value = formData.title;
            if (formData.first_name) document.getElementById('first_name').value = formData.first_name;
            if (formData.last_name) document.getElementById('last_name').value = formData.last_name;
            if (formData.email) document.getElementById('email').value = formData.email;
            if (formData.phone) document.getElementById('phone').value = formData.phone;
            if (formData.organization) document.getElementById('organization').value = formData.organization;
            if (formData.position) document.getElementById('position').value = formData.position;
            if (formData.passport_number) document.getElementById('passport_number').value = formData.passport_number;
            // Note: File inputs cannot be restored for security reasons
            if (formData.requires_visa) {
                if (formData.requires_visa === '1') {
                    document.getElementById('visa_yes').checked = true;
                } else if (formData.requires_visa === '0') {
                    document.getElementById('visa_no').checked = true;
                }
            }
            if (formData.address_line1) document.getElementById('address_line1').value = formData.address_line1;
            if (formData.city) document.getElementById('city').value = formData.city;
            if (formData.state) document.getElementById('state').value = formData.state;
            if (formData.country) document.getElementById('country').value = formData.country;
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
                // Set the hidden input value
                selectedPackageId.value = formData.package_id;
                
                const packageCard = document.querySelector(`[data-package-id="${formData.package_id}"]`);
                if (packageCard) {
                    // If form is already visible, just set the selected package without switching views
                    if (isFormVisible) {
                        selectedPackage = {
                            id: formData.package_id,
                            name: packageCard.dataset.packageName,
                            price: parseFloat(packageCard.dataset.packagePrice) || 0,
                            type: packageCard.dataset.packageType,
                            icon: packageCard.querySelector('.package-icon i')?.className || 'fas fa-ticket-alt',
                            color: packageCard.querySelector('.package-icon i')?.className.split(' ').find(cls => cls.startsWith('text-')) || 'text-primary'
                        };
                        updateSelectedPackageInfo();
                        updateParticipantFields();
                    } else {
                        selectPackage(packageCard);
                    }
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
                        // Ensure nationality dropdown is populated first
                        populateNationalitySelect();
                        // Then set the value
                        setTimeout(() => {
                            document.getElementById('nationality').value = formData.nationality;
                            // Trigger Select2 update
                            $('#nationality').trigger('change');
                        }, 100);
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
                            // Note: File inputs cannot be restored for security reasons
                            if (participant.requires_visa) {
                                const visaRadio = participantContainer.querySelector(`input[name*="[requires_visa]"][value="${participant.requires_visa}"]`);
                                if (visaRadio) visaRadio.checked = true;
                            }
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
    
    // Add form validation
    form.addEventListener('submit', function(e) {
        // Validate nationality field
        const nationalitySelect = document.getElementById('nationality');
        
        // Check if nationality dropdown is populated
        if (nationalitySelect.options.length <= 1) {
            e.preventDefault();
            showAlert('error', 'Nationality dropdown is not loaded. Please refresh the page and try again.', 'Loading Error');
            return false;
        }
        
        if (!nationalitySelect.value) {
            e.preventDefault();
            showAlert('error', 'Please select your nationality', 'Validation Error');
            nationalitySelect.focus();
            return false;
        }
        
        // Validate other required fields
        const requiredFields = [
            { id: 'first_name', name: 'First Name' },
            { id: 'last_name', name: 'Last Name' },
            { id: 'email', name: 'Email Address' },
            { id: 'phone', name: 'Phone Number' },
            { id: 'organization', name: 'Organization' }
        ];
        
        for (const field of requiredFields) {
            const element = document.getElementById(field.id);
            if (element && !element.value.trim()) {
                e.preventDefault();
                showAlert('error', `Please enter your ${field.name}`, 'Validation Error');
                element.focus();
                return false;
            }
        }
    });
    
    }, 100); // End of setTimeout
}); // End of DOMContentLoaded
