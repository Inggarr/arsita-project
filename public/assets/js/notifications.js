/**
 * ARSITA Notification System
 * Toast Notifications + Confirmation Modal
 * Menggantikan alert bawaan browser & confirm() dengan UI profesional
 */
(function() {
    'use strict';

    // ── Pastikan container toast tersedia ──
    function getToastContainer() {
        var c = document.querySelector('.arsita-toast-container');
        if (!c) {
            c = document.createElement('div');
            c.className = 'arsita-toast-container';
            document.body.appendChild(c);
        }
        return c;
    }

    // ── Mapping tipe ke ikon & judul ──
    var TOAST_CONFIG = {
        success: { icon: 'bi-check-circle-fill', title: 'Berhasil' },
        sukses:  { icon: 'bi-check-circle-fill', title: 'Berhasil' },
        danger:  { icon: 'bi-x-circle-fill',     title: 'Gagal' },
        error:   { icon: 'bi-x-circle-fill',     title: 'Gagal' },
        warning: { icon: 'bi-exclamation-triangle-fill', title: 'Perhatian' },
        info:    { icon: 'bi-info-circle-fill',  title: 'Informasi' }
    };

    /**
     * Tampilkan toast notification
     * @param {string} type - success|danger|error|warning|info
     * @param {string} message - Pesan notifikasi
     * @param {number} duration - Durasi tampil (ms), default 5000
     */
    window.arsitaToast = function(type, message, duration) {
        duration = duration || 5000;
        var config = TOAST_CONFIG[type] || TOAST_CONFIG.info;
        var container = getToastContainer();

        var toast = document.createElement('div');
        toast.className = 'arsita-toast toast-' + type;

        toast.innerHTML =
            '<div class="arsita-toast-icon"><i class="bi ' + config.icon + '"></i></div>' +
            '<div class="arsita-toast-body">' +
                '<div class="arsita-toast-title">' + config.title + '</div>' +
                '<div class="arsita-toast-message">' + message + '</div>' +
            '</div>' +
            '<button type="button" class="arsita-toast-close" aria-label="Tutup">' +
                '<i class="bi bi-x-lg"></i>' +
            '</button>' +
            '<div class="arsita-toast-progress" style="animation: arsitaToastProgress ' + duration + 'ms linear forwards;"></div>';

        container.appendChild(toast);

        // Close handler
        var closeBtn = toast.querySelector('.arsita-toast-close');
        function dismiss() {
            toast.classList.add('toast-closing');
            setTimeout(function() { toast.remove(); }, 300);
        }
        closeBtn.addEventListener('click', dismiss);

        // Auto dismiss
        var autoTimer = setTimeout(dismiss, duration);

        // Pause on hover
        toast.addEventListener('mouseenter', function() {
            clearTimeout(autoTimer);
            var prog = toast.querySelector('.arsita-toast-progress');
            if (prog) prog.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', function() {
            autoTimer = setTimeout(dismiss, 2000);
            var prog = toast.querySelector('.arsita-toast-progress');
            if (prog) prog.style.animationPlayState = 'running';
        });
    };


    // ── Modal Konfirmasi Profesional ──

    var MODAL_ICONS = {
        danger:  { icon: 'bi-exclamation-triangle-fill', cls: 'icon-danger' },
        warning: { icon: 'bi-question-circle-fill',      cls: 'icon-warning' },
        info:    { icon: 'bi-info-circle-fill',           cls: 'icon-info' },
        success: { icon: 'bi-check-circle-fill',          cls: 'icon-success' },
        logout:  { icon: 'bi-box-arrow-right',            cls: 'icon-warning' }
    };

    /**
     * Tampilkan modal konfirmasi profesional
     * @param {Object} options
     * @param {string} options.title - Judul modal
     * @param {string} options.message - Pesan konfirmasi
     * @param {string} [options.type='warning'] - danger|warning|info|success|logout
     * @param {string} [options.confirmText='Ya, Lanjutkan'] - Label tombol konfirmasi
     * @param {string} [options.cancelText='Batal'] - Label tombol batal
     * @param {string} [options.confirmClass='btn-primary'] - CSS class tombol konfirmasi
     * @param {Function} options.onConfirm - Callback jika dikonfirmasi
     * @param {Function} [options.onCancel] - Callback jika dibatalkan
     */
    window.arsitaConfirm = function(options) {
        var type = options.type || 'warning';
        var iconCfg = MODAL_ICONS[type] || MODAL_ICONS.warning;
        var confirmText = options.confirmText || 'Ya, Lanjutkan';
        var cancelText = options.cancelText || 'Batal';
        var confirmClass = options.confirmClass || (type === 'danger' ? 'btn-danger' : 'btn-primary');

        var overlay = document.createElement('div');
        overlay.className = 'arsita-modal-overlay';
        overlay.innerHTML =
            '<div class="arsita-modal-card">' +
                '<div class="arsita-modal-icon ' + iconCfg.cls + '">' +
                    '<i class="bi ' + iconCfg.icon + '"></i>' +
                '</div>' +
                '<div class="arsita-modal-title">' + (options.title || 'Konfirmasi') + '</div>' +
                '<div class="arsita-modal-message">' + (options.message || 'Apakah Anda yakin?') + '</div>' +
                '<div class="arsita-modal-actions">' +
                    '<button type="button" class="btn btn-secondary arsita-modal-cancel">' + cancelText + '</button>' +
                    '<button type="button" class="btn ' + confirmClass + ' arsita-modal-confirm">' + confirmText + '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        // Trigger animation
        requestAnimationFrame(function() {
            overlay.classList.add('modal-visible');
        });

        function closeModal(callback) {
            overlay.classList.remove('modal-visible');
            overlay.classList.add('modal-closing');
            setTimeout(function() {
                overlay.remove();
                if (typeof callback === 'function') callback();
            }, 300);
        }

        // Event listeners
        overlay.querySelector('.arsita-modal-cancel').addEventListener('click', function() {
            closeModal(options.onCancel || null);
        });

        overlay.querySelector('.arsita-modal-confirm').addEventListener('click', function() {
            closeModal(options.onConfirm || null);
        });

        // Close on overlay click (outside card)
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal(options.onCancel || null);
            }
        });

        // Close on Escape
        function escHandler(e) {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', escHandler);
                closeModal(options.onCancel || null);
            }
        }
        document.addEventListener('keydown', escHandler);

        // Focus tombol confirm
        setTimeout(function() {
            overlay.querySelector('.arsita-modal-confirm').focus();
        }, 100);
    };

    /**
     * Helper: pasang ke elemen <a> atau <button> dengan data-confirm
     * Cara pakai di HTML:
     *   <a href="/hapus.php?id=1" data-confirm="Hapus data ini?" data-confirm-title="Hapus Data"
     *      data-confirm-type="danger" data-confirm-btn="Ya, Hapus">Hapus</a>
     */
    document.addEventListener('click', function(e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;

        e.preventDefault();

        var msg   = el.getAttribute('data-confirm');
        var title = el.getAttribute('data-confirm-title') || 'Konfirmasi';
        var type  = el.getAttribute('data-confirm-type') || 'warning';
        var btn   = el.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
        var cls   = el.getAttribute('data-confirm-class') || (type === 'danger' ? 'btn-danger' : 'btn-primary');

        arsitaConfirm({
            title: title,
            message: msg,
            type: type,
            confirmText: btn,
            confirmClass: cls,
            onConfirm: function() {
                // Jika elemen adalah link <a>, navigasi ke hrefnya
                if (el.tagName === 'A' && el.href) {
                    window.location.href = el.href;
                }
                // Jika elemen adalah <button> di form, submit formnya
                else if (el.tagName === 'BUTTON' && el.form) {
                    // Jika button punya name/value, tambahkan hidden input
                    if (el.name) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = el.name;
                        hidden.value = el.value || '';
                        el.form.appendChild(hidden);
                    }
                    el.form.submit();
                }
            }
        });
    });

})();
