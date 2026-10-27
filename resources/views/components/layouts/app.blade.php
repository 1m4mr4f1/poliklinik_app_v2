<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Poliklinik' }} | Poliklinik App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Dialog konfirmasi kustom */
        #confirm-dialog::backdrop {
            background-color: rgb(15 23 42 / 0.45);
        }
        #confirm-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            border: 1px solid var(--color-clinic-border);
            border-radius: var(--radius-control);
            background-color: var(--color-clinic-surface);
            padding: 0;
            width: min(420px, calc(100vw - 32px));
            box-shadow: 0 8px 32px rgb(0 0 0 / 0.12);
            max-height: calc(100vh - 32px);
            overflow: auto;
        }
        #confirm-dialog .confirm-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px 0;
        }
        #confirm-dialog .confirm-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-control);
            flex-shrink: 0;
        }
        #confirm-dialog .confirm-icon.danger {
            background-color: var(--color-clinic-danger-bg);
            color: var(--color-clinic-danger);
        }
        #confirm-dialog .confirm-icon.warning {
            background-color: #fef3c7;
            color: var(--color-clinic-waiting);
        }
        #confirm-dialog .confirm-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-clinic-text);
            line-height: 1.3;
        }
        #confirm-dialog .confirm-body {
            padding: 10px 20px 16px;
            color: var(--color-clinic-secondary);
            font-size: 13px;
            line-height: 1.5;
        }
        #confirm-dialog .confirm-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 20px;
            border-top: 1px solid var(--color-clinic-border);
            background-color: var(--color-clinic-background);
            border-radius: 0 0 var(--radius-control) var(--radius-control);
        }
    </style>
</head>

<body>

    {{-- DIALOG KONFIRMASI GLOBAL --}}
    <dialog id="confirm-dialog" aria-modal="true" aria-labelledby="confirm-dialog-title">
        <div class="confirm-header">
            <span class="confirm-icon" id="confirm-dialog-icon" aria-hidden="true">
                <i class="fas fa-exclamation-triangle"></i>
            </span>
            <p class="confirm-title" id="confirm-dialog-title">Konfirmasi</p>
        </div>
        <p class="confirm-body" id="confirm-dialog-message"></p>
        <div class="confirm-footer">
            <button type="button" id="confirm-cancel-btn" class="ui-button ui-button-secondary">Batal</button>
            <button type="button" id="confirm-ok-btn" class="ui-button ui-button-danger">Ya, lanjutkan</button>
        </div>
    </dialog>

    <div class="app-wrapper">

        {{-- SIDEBAR --}}
        <div id="appSidebar" class="sidebar-fixed">
            @include('components.partials.sidebar')
        </div>

        {{-- OVERLAY --}}
        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true" onclick="toggleSidebar()"></div>

        {{-- MAIN --}}
        <div class="main-content">

            @include('components.partials.header')

            <div class="main-scroll">

                @if(session('success'))
                    <div class="ui-alert-success mb-4 flex items-center gap-2" role="status">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <x-ui.error-state
                        :message="session('error')"
                        class="mb-4" />
                @endif

                @if($errors->any())
                    <div class="ui-alert-error mb-4 flex items-center gap-2" role="alert">
                        <i class="fas fa-circle-exclamation"></i>
                        <span>Ada kesalahan pada data yang dikirim. Periksa kembali isian yang ditandai.</span>
                    </div>
                @endif

                <div id="pageContent">
                    {{ $slot }}
                </div>
                <div id="pageLoadingState" hidden>
                    <x-ui.loading-state rows="6" />
                </div>

            </div>

            @include('components.partials.footer')

        </div>

    </div>

    <script>
        const appWrapper = document.querySelector('.app-wrapper');
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileSidebarQuery = window.matchMedia('(max-width: 1024px)');

        function setSidebarOpen(isOpen) {
            const isMobile = mobileSidebarQuery.matches;

            if (isMobile) {
                appWrapper.classList.remove('sidebar-collapsed');
                sidebar.hidden = !isOpen;
                overlay.style.display = isOpen ? 'block' : 'none';
                document.body.classList.toggle('overflow-hidden', isOpen);
            } else {
                sidebar.hidden = false;
                appWrapper.classList.toggle('sidebar-collapsed', !isOpen);
                overlay.style.display = 'none';
                document.body.classList.remove('overflow-hidden');
            }

            sidebar.inert = !isOpen;

            const label = isOpen
                ? (isMobile ? 'Tutup menu' : 'Sembunyikan menu')
                : 'Tampilkan menu';

            sidebarToggle.setAttribute('aria-expanded', String(isOpen));
            sidebarToggle.setAttribute('aria-label', label);
            sidebarToggle.setAttribute('title', label);
        }

        function toggleSidebar() {
            const isOpen = sidebarToggle.getAttribute('aria-expanded') === 'true';
            setSidebarOpen(!isOpen);
        }

        function syncSidebarForViewport() {
            if (mobileSidebarQuery.matches) {
                setSidebarOpen(false);
                return;
            }

            setSidebarOpen(!appWrapper.classList.contains('sidebar-collapsed'));
        }

        mobileSidebarQuery.addEventListener('change', syncSidebarForViewport);
        window.addEventListener('resize', syncSidebarForViewport);
        syncSidebarForViewport();

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileSidebarQuery.matches) {
                setSidebarOpen(false);
            }
        });

        document.querySelectorAll('.ui-table').forEach((table) => {
            const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());

            table.querySelectorAll('tbody tr').forEach((row) => {
                Array.from(row.children).forEach((cell, index) => {
                    if (!cell.hasAttribute('data-label') && headers[index] !== undefined) {
                        cell.setAttribute('data-label', headers[index]);
                    }
                });
            });
        });

        const pageContent = document.getElementById('pageContent');
        const pageLoadingState = document.getElementById('pageLoadingState');

        function showPageLoadingState() {
            if (pageContent && pageLoadingState) {
                pageContent.hidden = true;
                pageLoadingState.hidden = false;
            }
        }

        window.addEventListener('pageshow', () => {
            if (pageContent && pageLoadingState) {
                pageContent.hidden = false;
                pageLoadingState.hidden = true;
            }
        });

        // ============================================================
        // DIALOG KONFIRMASI KUSTOM
        // ============================================================
        const confirmDialog = document.getElementById('confirm-dialog');
        const confirmMessage = document.getElementById('confirm-dialog-message');
        const confirmTitle = document.getElementById('confirm-dialog-title');
        const confirmIcon = document.getElementById('confirm-dialog-icon');
        const confirmOkBtn = document.getElementById('confirm-ok-btn');
        const confirmCancelBtn = document.getElementById('confirm-cancel-btn');

        function showConfirm({ message, title = 'Konfirmasi', type = 'danger', okText = 'Ya, lanjutkan' }) {
            confirmTitle.textContent = title;
            confirmMessage.textContent = message;
            confirmOkBtn.textContent = okText;

            confirmIcon.className = `confirm-icon ${type}`;
            confirmIcon.innerHTML = type === 'warning'
                ? '<i class="fas fa-exclamation-triangle"></i>'
                : '<i class="fas fa-trash-alt"></i>';

            confirmOkBtn.className = `ui-button ${type === 'warning' ? 'ui-button-primary' : 'ui-button-danger'}`;

            return new Promise((resolve) => {
                confirmDialog.showModal();

                const onOk = () => { cleanup(); resolve(true); };
                const onCancel = () => { cleanup(); resolve(false); };
                const onBackdrop = (e) => {
                    if (e.target === confirmDialog) { cleanup(); resolve(false); }
                };

                function cleanup() {
                    confirmDialog.close();
                    confirmOkBtn.removeEventListener('click', onOk);
                    confirmCancelBtn.removeEventListener('click', onCancel);
                    confirmDialog.removeEventListener('click', onBackdrop);
                    confirmDialog.removeEventListener('cancel', onCancel);
                }

                confirmOkBtn.addEventListener('click', onOk);
                confirmCancelBtn.addEventListener('click', onCancel);
                confirmDialog.addEventListener('click', onBackdrop);
                confirmDialog.addEventListener('cancel', onCancel);
            });
        }

        // Intercept semua form yang punya data-confirm
        document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.method === 'dialog') {
                return;
            }

            const message = form.dataset.confirm;
            if (message) {
                event.preventDefault();
                const type = form.dataset.confirmType ?? 'danger';
                const title = form.dataset.confirmTitle ?? 'Konfirmasi';
                const okText = form.dataset.confirmOk ?? 'Ya, lanjutkan';

                const confirmed = await showConfirm({ message, title, type, okText });
                if (confirmed) {
                    delete form.dataset.confirm;
                    showPageLoadingState();
                    form.requestSubmit();
                }
            }
        });

        document.querySelectorAll('[data-table-search]').forEach((searchInput) => {
            const tableId = searchInput.dataset.tableSearch;
            const table = document.getElementById(tableId);
            const filterInput = document.querySelector(`[data-table-filter="${tableId}"]`);
            const noResults = document.querySelector(`[data-table-no-results="${tableId}"]`);

            if (!table) {
                return;
            }

            const updateRows = () => {
                const searchTerm = searchInput.value.trim().toLocaleLowerCase('id');
                const filterValue = filterInput?.value ?? '';
                let visibleRows = 0;

                table.querySelectorAll('tbody tr[data-search]').forEach((row) => {
                    const matchesSearch = row.dataset.search.includes(searchTerm);
                    const matchesFilter = !filterValue || row.dataset.filter === filterValue;
                    row.hidden = !matchesSearch || !matchesFilter;
                    visibleRows += row.hidden ? 0 : 1;
                });

                if (noResults) {
                    const hasNoResults = visibleRows === 0;
                    noResults.hidden = !hasNoResults;
                    noResults.classList.toggle('hidden', !hasNoResults);
                }
            };

            searchInput.addEventListener('input', updateRows);
            filterInput?.addEventListener('change', updateRows);
        });
    </script>

    @stack('scripts')

</body>

</html>
