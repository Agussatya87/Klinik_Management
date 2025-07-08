// Klinik Management System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Search functionality enhancement
    var searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.target.closest('form').submit();
            }
        });
    });

    // Table row selection
    var tableRows = document.querySelectorAll('.table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('td:last-child')) {
                this.classList.toggle('table-active');
            }
        });
    });

    // Dynamic form field validation
    function validateFormField(field) {
        var value = field.value.trim();
        var isValid = true;
        var errorMessage = '';

        // Remove existing validation classes
        field.classList.remove('is-valid', 'is-invalid');

        // Check if field is required
        if (field.hasAttribute('required') && value === '') {
            isValid = false;
            errorMessage = 'Field ini wajib diisi';
        }

        // Email validation
        if (field.type === 'email' && value !== '') {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format email tidak valid';
            }
        }

        // Phone number validation
        if (field.name === 'telepon' && value !== '') {
            var phoneRegex = /^[\d\s\-\+\(\)]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format nomor telepon tidak valid';
            }
        }

        // Number validation
        if (field.type === 'number' && value !== '') {
            if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.getAttribute('min'))) {
                isValid = false;
                errorMessage = 'Nilai minimum adalah ' + field.getAttribute('min');
            }
            if (field.hasAttribute('max') && parseFloat(value) > parseFloat(field.getAttribute('max'))) {
                isValid = false;
                errorMessage = 'Nilai maksimum adalah ' + field.getAttribute('max');
            }
        }

        // Apply validation classes
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
            // Show error message
            var errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.textContent = errorMessage;
            }
        }

        return isValid;
    }

    // Add validation to form fields
    var formFields = document.querySelectorAll('input, select, textarea');
    formFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            validateFormField(this);
        });

        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateFormField(this);
            }
        });
    });

    // Confirm delete functionality
    window.confirmDelete = function(id, name) {
        var modal = document.getElementById('deleteModal');
        if (modal) {
            var nameElement = modal.querySelector('#patientName, #procedureName, #diagnosisName, #doctorName, #roomName');
            var idElement = modal.querySelector('#patientId, #procedureId, #diagnosisId, #doctorId, #roomId');
            
            if (nameElement) nameElement.textContent = name;
            if (idElement) idElement.value = id;
            
            var bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    };

    // Loading state for buttons
    var submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (this.closest('form').checkValidity()) {
                this.disabled = true;
                this.innerHTML = '<span class="loading"></span> Loading...';
            }
        });
    });

    // Auto-generate patient number
    var patientForm = document.querySelector('form[action*="pasien"]');
    if (patientForm) {
        var noRmField = patientForm.querySelector('input[name="no_rm"]');
        if (noRmField && !noRmField.value) {
            // Generate RM number based on current date and time
            var now = new Date();
            var year = now.getFullYear();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');
            var time = String(now.getHours()).padStart(2, '0') + String(now.getMinutes()).padStart(2, '0');
            var rmNumber = 'RM' + year + month + day + time;
            noRmField.value = rmNumber;
        }
    }

    // Date picker enhancement
    var dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });

    // Time picker enhancement
    var timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(function(input) {
        if (!input.value) {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = hours + ':' + minutes;
        }
    });

    // Print functionality
    window.printPage = function() {
        window.print();
    };

    // Export to CSV functionality
    window.exportToCSV = function(tableId, filename) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var csv = [];
        var rows = table.querySelectorAll('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (var j = 0; j < cols.length; j++) {
                // Get text content without HTML tags
                var text = cols[j].textContent || cols[j].innerText;
                // Escape quotes and wrap in quotes if contains comma
                if (text.includes(',')) {
                    text = '"' + text.replace(/"/g, '""') + '"';
                }
                row.push(text);
            }
            
            csv.push(row.join(','));
        }

        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        
        if (link.download !== undefined) {
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };

    // Responsive table enhancement
    var tables = document.querySelectorAll('.table-responsive');
    tables.forEach(function(table) {
        var wrapper = table.parentNode;
        if (wrapper && wrapper.classList.contains('card-body')) {
            wrapper.style.overflowX = 'auto';
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N for new record
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            var addButton = document.querySelector('a[href*="action=add"]');
            if (addButton) {
                window.location.href = addButton.href;
            }
        }
        
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            var searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape key to close modals
        if (e.key === 'Escape') {
            var modals = document.querySelectorAll('.modal.show');
            modals.forEach(function(modal) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            });
        }
    });

    // Smooth scrolling for anchor links
    var anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-save form data to localStorage
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        var formId = form.id || 'form_' + Math.random().toString(36).substr(2, 9);
        form.id = formId;
        
        // Load saved data
        var savedData = localStorage.getItem('form_' + formId);
        if (savedData) {
            var data = JSON.parse(savedData);
            Object.keys(data).forEach(function(key) {
                var field = form.querySelector('[name="' + key + '"]');
                if (field && !field.value) {
                    field.value = data[key];
                }
            });
        }
        
        // Save data on input
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                var formData = {};
                inputs.forEach(function(field) {
                    if (field.name) {
                        formData[field.name] = field.value;
                    }
                });
                localStorage.setItem('form_' + formId, JSON.stringify(formData));
            });
        });
        
        // Clear saved data on successful submit
        form.addEventListener('submit', function() {
            localStorage.removeItem('form_' + formId);
        });
    });

    console.log('Klinik Management System initialized successfully!');
}); 

// Responsive enhancements for mobile

document.addEventListener('DOMContentLoaded', function() {
    // 1. Auto-collapse navbar on nav-link click (mobile)
    var navbarCollapse = document.getElementById('navbarNav');
    if (navbarCollapse) {
        var navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    var bsCollapse = bootstrap.Collapse.getOrCreateInstance(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
    }

    // 2. Dynamic font resize for dashboard cards on very small screens
    function adjustDashboardCardFont() {
        var cards = document.querySelectorAll('.dashboard-cards .card');
        if (window.innerWidth < 400) {
            cards.forEach(function(card) {
                card.style.fontSize = '0.92rem';
            });
        } else {
            cards.forEach(function(card) {
                card.style.fontSize = '';
            });
        }
    }
    window.addEventListener('resize', adjustDashboardCardFont);
    adjustDashboardCardFont();

    // 3. Touch feedback (ripple effect) for dashboard cards
    var dashboardCards = document.querySelectorAll('.dashboard-cards .card');
    dashboardCards.forEach(function(card) {
        card.addEventListener('touchstart', function(e) {
            card.classList.add('shadow-lg');
        });
        card.addEventListener('touchend', function(e) {
            setTimeout(function() {
                card.classList.remove('shadow-lg');
            }, 180);
        });
        card.addEventListener('touchcancel', function(e) {
            card.classList.remove('shadow-lg');
        });
    });
}); 