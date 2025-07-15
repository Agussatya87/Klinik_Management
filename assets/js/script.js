(function () {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    // 🔁 UI Bootstrap
    function initBootstrapUI() {
        $$('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        $$('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
    }

    // // ⏳ Loading on Submit
    // function setupSubmitLoading() {
    //     $$('form button[type="submit"]').forEach(button => {
    //         const form = button.closest('form');
    //         button.addEventListener('click', () => {
    //             if (form && form.checkValidity()) {
    //                 button.disabled = true;
    //                 button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    //             }
    //         });
    //     });
    // }

    // // ⚠️ Form Validasi
    // function setupFormValidation() {
    //     $$('.needs-validation').forEach(form => {
    //         form.addEventListener('submit', e => {
    //             console.log('Form valid?', form.checkValidity());

    //             if (!form.checkValidity()) {
    //                 e.preventDefault();
    //                 e.stopPropagation();
    //                 console.log('Form submit dicegah karena invalid!');

    //                 const elements = Array.from(form.elements);
    //                 const invalidFields = elements.filter(el => !el.checkValidity());

    //                 if (invalidFields.length > 0) {
    //                     // Fokus ke field invalid pertama
    //                     invalidFields[0].focus();

    //                     // Log error tiap field
    //                     invalidFields.forEach(el => {
    //                         const label = form.querySelector(`label[for="${el.id}"]`);
    //                         const labelText = label ? label.innerText : el.name || el.id || 'unknown';
    //                         console.warn(`⚠️ Field "${labelText}" invalid: ${el.validationMessage}`);
    //                     });
    //                 }
    //             }

    //             form.classList.add('was-validated');
    //         });
    //     });
    // }

    // 🗑️ Auto Dismiss Alert (3 detik)
    function autoDismissAlerts(timeout = 3000) {
        const alerts = $$('.alert-dismissible');
        if (alerts.length === 0) return;

        setTimeout(() => {
            alerts.forEach(alert => {
                try {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                } catch (err) {
                    console.warn('❌ Gagal dismiss alert:', err);
                }
            });
        }, timeout);
    }

    // 📅 Default Tanggal/Waktu
    function defaultDateTime() {
        const now = new Date();
        const today = now.toISOString().split('T')[0];

        $$('input[type="date"]').forEach(input => {
            if (!input.value) input.value = today;
        });

        $$('input[type="time"]').forEach(input => {
            if (!input.value) {
                input.value = now.toLocaleTimeString('it-IT', {
                    hour: '2-digit', minute: '2-digit'
                });
            }
        });
    }

    // 🖱️ Highlight Table Baris
    function enableRowHighlight() {
        $$('.table tbody tr').forEach(row => {
            row.addEventListener('click', e => {
                if (!e.target.closest('td:last-child')) {
                    row.classList.toggle('table-active');
                }
            });
        });
    }

    // 🔎 Search Submit on Enter
    function setupSearchSubmit() {
        $$('input[name="search"]').forEach(input => {
            input.addEventListener('keypress', e => {
                if (e.key === 'Enter') input.closest('form').submit();
            });
        });
    }

    // 🗑️ Konfirmasi Hapus (modal dinamis)
    window.confirmDelete = function (id, name) {
        const modal = $('#deleteModal');
        if (!modal) return;

        const nameSpan = modal.querySelector('#patientName, #procedureName, #diagnosisName, #doctorName, #roomName');
        const idInput = modal.querySelector('#patientId, #procedureId, #diagnosisId, #doctorId, #roomId');

        if (nameSpan) nameSpan.textContent = name;
        if (idInput) idInput.value = id;

        new bootstrap.Modal(modal).show();
    };

    // ⌨️ Keyboard Shortcuts
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const link = $('a[href*="action=add"]');
                if (link) window.location.href = link.href;
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                $('input[name="search"]')?.focus();
            }
            if (e.key === 'Escape') {
                $$('.modal.show').forEach(modal => {
                    const instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                });
            }
        });
    }

    // 🎯 Scroll Smooth Internal Anchor
    function setupSmoothScroll() {
        $$('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', e => {
                const target = $(anchor.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // 🚀 Inisialisasi Utama
    function init() {
        initBootstrapUI();
        // setupFormValidation();
        // setupSubmitLoading();
        autoDismissAlerts(); // Flash alert auto close after 3s
        defaultDateTime();
        enableRowHighlight();
        setupSearchSubmit();
        setupKeyboardShortcuts();
        setupSmoothScroll();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
