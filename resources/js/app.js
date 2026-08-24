import './bootstrap';
import 'bootstrap';

// Import AlpineJS
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Import other globally needed libraries
import Swal from 'sweetalert2';
window.Swal = Swal;

// Global SweetAlert2 confirmation helper
window.confirmAction = function (options) {
    const defaultOptions = {
        title: 'Apakah Anda yakin?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    };

    const finalOptions = { ...defaultOptions, ...options };
    return Swal.fire(finalOptions);
};

// Global helper for delete confirmations
window.confirmDelete = function (formOrCallback, itemName = 'data ini') {
    return Swal.fire({
        title: 'Hapus ' + itemName + '?',
        text: 'Data yang dihapus tidak dapat dipulihkan kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof formOrCallback === 'function') {
                formOrCallback();
            } else if (formOrCallback && typeof formOrCallback.submit === 'function') {
                formOrCallback.submit();
            } else if (typeof formOrCallback === 'string') {
                const form = document.getElementById(formOrCallback);
                if (form) form.submit();
            }
        }
    });
};

// DOM Content Loaded enhancements
document.addEventListener('DOMContentLoaded', function () {
    // Form submit loading state handler
    document.querySelectorAll('form:not([data-no-loading])').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }
            const submitBtn = form.querySelector('button[type="submit"]:not([data-no-loading])');
            if (submitBtn && !submitBtn.disabled) {
                const loadingText = submitBtn.getAttribute('data-loading-text') || 'Menyimpan...';
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                
                // Keep original content in case of back navigation
                if (!submitBtn.getAttribute('data-original-html')) {
                    submitBtn.setAttribute('data-original-html', submitBtn.innerHTML);
                }
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`;
            }
        });
    });
});

