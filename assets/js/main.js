// ============================================
// PETAL - Main JavaScript
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // 1. TYPE SELECTOR (Private / Public on write page)
    // ============================================
    const typeOptions = document.querySelectorAll('.type-option');
    const privateFields = document.getElementById('private-fields');
    const publicFields = document.getElementById('public-fields');
    const messageTypeInput = document.getElementById('message_type');

    if (typeOptions.length > 0) {
        typeOptions.forEach(option => {
            option.addEventListener('click', function () {
                // Remove active from all
                typeOptions.forEach(o => o.classList.remove('active'));
                // Add active to clicked
                this.classList.add('active');
                
                const type = this.dataset.type;
                if (messageTypeInput) messageTypeInput.value = type;

                // Toggle fields
                if (type === 'private') {
                    privateFields.style.display = 'block';
                    publicFields.style.display = 'none';
                } else {
                    privateFields.style.display = 'none';
                    publicFields.style.display = 'block';
                }
            });
        });
    }

    // ============================================
    // 2. FORM VALIDATION
    // ============================================
    const writeForm = document.getElementById('write-form');

    if (writeForm) {
        writeForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let isValid = true;
            const type = messageTypeInput ? messageTypeInput.value : 'public';

            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

            if (type === 'private') {
                // Validate: sender_name
                const senderName = document.getElementById('sender_name');
                if (!senderName || senderName.value.trim().length < 2) {
                    showError('error-sender', 'Nama minimal 2 karakter.');
                    if (senderName) senderName.classList.add('is-invalid');
                    isValid = false;
                }

                // Validate: email
                const email = document.getElementById('email_tujuan');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email.value.trim())) {
                    showError('error-email', 'Masukkan email yang valid.');
                    if (email) email.classList.add('is-invalid');
                    isValid = false;
                }

                // Validate: tanggal_kirim (must be in future)
                const tanggal = document.getElementById('tanggal_kirim');
                const today = new Date();
                today.setHours(0,0,0,0);
                if (!tanggal || new Date(tanggal.value) <= today) {
                    showError('error-tanggal', 'Tanggal harus di masa depan.');
                    if (tanggal) tanggal.classList.add('is-invalid');
                    isValid = false;
                }

                // Validate: pesan
                const pesan = document.getElementById('pesan_private');
                if (!pesan || pesan.value.trim().length < 10) {
                    showError('error-pesan-private', 'Pesan minimal 10 karakter.');
                    if (pesan) pesan.classList.add('is-invalid');
                    isValid = false;
                }

            } else {
                // Validate: untuk_siapa
                const untuk = document.getElementById('untuk_siapa');
                if (!untuk || untuk.value.trim().length < 2) {
                    showError('error-untuk', 'Isi "Untuk siapa" minimal 2 karakter.');
                    if (untuk) untuk.classList.add('is-invalid');
                    isValid = false;
                }

                // Validate: pesan public
                const pesanPub = document.getElementById('pesan_public');
                if (!pesanPub || pesanPub.value.trim().length < 10) {
                    showError('error-pesan-public', 'Pesan minimal 10 karakter.');
                    if (pesanPub) pesanPub.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (isValid) {
                writeForm.submit();
            }
        });
    }

    // ============================================
    // 3. DELETE CONFIRMATION
    // ============================================
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const confirmed = confirm('Yakin ingin menghapus pesan ini? Tindakan ini tidak dapat dibatalkan.');
            if (confirmed) {
                window.location.href = this.href;
            }
        });
    });

    // ============================================
    // 4. SEARCH FILTER (client-side for public wall)
    // ============================================
    const searchInput = document.getElementById('search-wall');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.petal-card[data-searchable]').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.closest('.col').style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ============================================
    // 5. AUTO DISMISS ALERTS
    // ============================================
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // ============================================
    // 6. CHARACTER COUNTER FOR TEXTAREA
    // ============================================
    document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
        const maxLen = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end mt-1';
        counter.textContent = `0 / ${maxLen}`;
        textarea.parentNode.appendChild(counter);

        textarea.addEventListener('input', function () {
            counter.textContent = `${this.value.length} / ${maxLen}`;
        });
    });

    // ============================================
    // 7. NAVBAR SHRINK ON SCROLL
    // ============================================
    const navWrap = document.querySelector('.floating-nav-wrap');
    if (navWrap) {
        const SCROLL_THRESHOLD = 60;
        window.addEventListener('scroll', function () {
            if (window.scrollY > SCROLL_THRESHOLD) {
                navWrap.classList.add('scrolled');
            } else {
                navWrap.classList.remove('scrolled');
            }
        });
    }

});
function showError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
}