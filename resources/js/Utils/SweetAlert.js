import Swal from 'sweetalert2';

const SweetAlert = Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-primary px-6 py-2.5',
        cancelButton: 'btn btn-secondary px-6 py-2.5 ml-3',
        popup: 'rounded-[2rem] border-none shadow-2xl',
        title: 'text-2xl font-black text-slate-900 dark:text-white pt-8 px-6',
        htmlContainer: 'text-slate-600 dark:text-slate-400 text-base font-medium px-8 leading-relaxed',
        actions: 'pb-10 pt-4 gap-4',
        icon: 'border-none scale-110 mt-8'
    },
    buttonsStyling: false,
    showClass: {
        popup: 'animate__animated animate__zoomIn animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__zoomOut animate__faster'
    },
    heightAuto: false,
    width: 'min(480px, 92%)'
});

export const notify = {
    success(title, text = '') {
        return Swal.fire({
            icon: 'success',
            title,
            text,
            iconColor: '#10B981',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            backdrop: false,
            position: 'top-end',
            width: 'auto',
            iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            customClass: {
                popup: 'rounded-xl shadow-lg border-none',
                title: 'text-sm font-bold text-slate-800 dark:text-white',
                htmlContainer: 'text-xs text-slate-500 dark:text-slate-400'
            }
        });
    },
    error(title, text = '') {
        return Swal.fire({
            icon: 'error',
            title,
            text,
            iconColor: '#EF4444',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            backdrop: false,
            position: 'top-end',
            width: 'auto',
            iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            customClass: {
                popup: 'rounded-xl shadow-lg border-none',
                title: 'text-sm font-bold text-slate-800 dark:text-white',
                htmlContainer: 'text-xs text-slate-500 dark:text-slate-400'
            }
        });
    },
    confirm(title, text = '', confirmButtonText = 'Ya, Lanjutkan', icon = 'warning') {
        return SweetAlert.fire({
            title,
            text,
            icon,
            iconColor: icon === 'warning' ? '#F59E0B' : '#7C3AED',
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: `rgba(15, 12, 41, 0.4)`
        });
    },
    alert(title, text = '', icon = 'info') {
        if (typeof title === 'object') {
            return SweetAlert.fire({
                iconColor: '#7C3AED',
                confirmButtonText: 'OK',
                backdrop: `rgba(15, 12, 41, 0.4)`,
                ...title
            });
        }

        let iconColor = '#7C3AED';
        if (icon === 'success') iconColor = '#10B981';
        if (icon === 'error') iconColor = '#EF4444';
        if (icon === 'warning') iconColor = '#F59E0B';

        return SweetAlert.fire({
            title,
            text,
            icon,
            iconColor,
            confirmButtonText: 'OK',
            backdrop: `rgba(15, 12, 41, 0.4)`
        });
    }
};

export default SweetAlert;
