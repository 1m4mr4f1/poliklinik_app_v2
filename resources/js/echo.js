import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY;

if (key) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

const queueChannels = new Set(
    [...document.querySelectorAll('[data-queue-channel]')]
        .map((element) => element.dataset.queueChannel)
        .filter(Boolean),
);

queueChannels.forEach((channel) => {
    if (!window.Echo) {
        return;
    }

    window.Echo.channel(channel).listen('.antrian.diperbarui', (event) => {
        document.querySelectorAll(`[data-queue-channel="${channel}"] [data-queue-number="${event.id_jadwal}"]`)
            .forEach((numberElement) => {
                numberElement.textContent = event.nomor_dipanggil;
            });

        if (event.nomor_diperiksa !== null && event.nomor_diperiksa !== undefined) {
            document.querySelectorAll(`[data-queue-channel="${channel}"] [data-examined-number="${event.id_jadwal}"]`)
                .forEach((numberElement) => {
                    numberElement.textContent = event.nomor_diperiksa;
                });
        }

        if (event.nomor_diperiksa !== null
            && event.nomor_diperiksa !== undefined
            && Number(event.nomor_dipanggil) === Number(event.nomor_diperiksa)) {
            document.querySelectorAll(`[data-queue-channel="${channel}"] [data-exam-registration]`)
                .forEach((row) => {
                    if (Number(row.dataset.examNumber) !== Number(event.nomor_diperiksa)) {
                        return;
                    }

                    const status = row.querySelector('[data-exam-status]');
                    if (status) {
                        status.textContent = 'Sudah diperiksa';
                    }

                    const action = row.querySelector('[data-exam-action]');
                    if (action) {
                        if (row.dataset.examDetailUrl) {
                            const detailLink = document.createElement('a');
                            detailLink.href = row.dataset.examDetailUrl;
                            detailLink.className = 'font-semibold text-indigo-700 hover:text-indigo-900';
                            detailLink.dataset.examAction = '';
                            detailLink.textContent = 'Detail';
                            action.replaceWith(detailLink);
                        } else {
                            action.textContent = 'Selesai';
                            action.removeAttribute('href');
                            action.classList.add('text-slate-400');
                        }
                    }
                });

        }

        if (event.jumlah_belum_diperiksa !== null && event.jumlah_belum_diperiksa !== undefined) {
            document.querySelectorAll(`[data-queue-channel="${channel}"] [data-pending-count="${event.id_jadwal}"]`)
                .forEach((countElement) => {
                    countElement.textContent = event.jumlah_belum_diperiksa;
                });
        }

        document.querySelectorAll(`[data-exam-link="${event.id_jadwal}"]`).forEach((examLink) => {
            const isReadyToExamine = Number(event.nomor_dipanggil) !== Number(event.nomor_diperiksa)
                && Number(examLink.dataset.examNumber) === Number(event.nomor_dipanggil);
            examLink.classList.toggle('hidden', !isReadyToExamine);
            examLink.classList.toggle('inline-flex', isReadyToExamine);
        });
    });
});

const appointmentDate = document.querySelector('[data-appointment-date]');
const scheduleSelect = document.querySelector('[data-appointment-schedule]');

if (appointmentDate && scheduleSelect) {
    const syncSchedulesToDate = () => {
        const selectedDate = appointmentDate.value;
        const selectedDay = selectedDate
            ? new Date(`${selectedDate}T00:00:00`).getDay()
            : null;

        scheduleSelect.querySelectorAll('option[data-weekday]').forEach((option) => {
            option.hidden = selectedDay === null || Number(option.dataset.weekday) !== selectedDay;
        });

        const selectedOption = scheduleSelect.selectedOptions[0];
        if (selectedOption?.hidden) {
            scheduleSelect.value = '';
        }
    };

    appointmentDate.addEventListener('change', syncSchedulesToDate);
    syncSchedulesToDate();
}
