(function () {
    'use strict';

    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    // 🔁 UI Bootstrap
    function initBootstrapUI() {
        $$('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        $$('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
    }

    // ⏳ Loading on Submit
    function setupSubmitLoading() {
        $$('form button[type="submit"]').forEach(button => {
            const form = button.closest('form');
            button.addEventListener('click', () => {
                if (form && form.checkValidity()) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
                }
            });
        });
    }

    // ⚠️ Form Validasi
    function setupFormValidation() {
        $$('.needs-validation').forEach(form => {
            form.addEventListener('submit', e => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // 🗑️ Auto Dismiss Alert
    function autoDismissAlerts(timeout = 5000) {
        setTimeout(() => {
            $$('.alert').forEach(a => new bootstrap.Alert(a).close());
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

    // 💾 Auto Save Form ke LocalStorage
    function autoSaveForms() {
        $$('form').forEach(form => {
            const formId = form.id || `form_${Math.random().toString(36).slice(2, 9)}`;
            form.id = formId;

            const saved = localStorage.getItem('form_' + formId);
            if (saved) {
                Object.entries(JSON.parse(saved)).forEach(([k, v]) => {
                    const f = form.elements.namedItem(k);
                    if (f && !f.value) f.value = v;
                });
            }

            form.addEventListener('input', () => {
                const data = {};
                Array.from(form.elements).forEach(e => {
                    if (e.name) data[e.name] = e.value;
                });
                localStorage.setItem('form_' + formId, JSON.stringify(data));
            });

            form.addEventListener('submit', () => {
                localStorage.removeItem('form_' + formId);
            });
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

    // 📤 Export Table ke CSV
    window.exportToCSV = function (tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tr'));
        const csv = rows.map(row =>
            Array.from(row.querySelectorAll('td, th')).map(cell => {
                let text = cell.textContent.trim();
                if (text.includes(',')) text = `"${text.replace(/"/g, '""')}"`;
                return text;
            }).join(',')
        ).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${filename}.csv`;
        link.click();
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
        setupFormValidation();
        setupSubmitLoading();
        autoDismissAlerts();
        defaultDateTime();
        autoSaveForms();
        enableRowHighlight();
        setupSearchSubmit();
        setupKeyboardShortcuts();
        setupSmoothScroll();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
