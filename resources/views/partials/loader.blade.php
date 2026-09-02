@php
    $targetNotificationId = $notificationId ?? 'notification';
    $targetLoaderId = $loaderId ?? 'setLoader';
    $targetLoaderText = $loaderText ?? 'Processing your request, please wait...';
@endphp

<!-- Alert & Notification Banner Container -->
<div id="{{ $targetNotificationId }}_notification" class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3 notification-container" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-octagon-fill fs-4 me-3 text-danger flex-shrink-0" id="{{ $targetNotificationId }}_notification_icon"></i>
        <div class="flex-grow-1">
            <p id="{{ $targetNotificationId }}_notification_error" class="mb-0 text-dark fw-medium"></p>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('{{ $targetNotificationId }}_notification').style.display='none';" aria-label="Close"></button>
    </div>
</div>

<!-- Modern Animated Spinner / Loader -->
<div id="{{ $targetLoaderId }}" class="app-loader-container text-center my-3" style="display: none;" aria-live="polite" aria-busy="true">
    <div class="d-inline-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm bg-white border" style="min-width: 220px;">
        <div class="spinner-border text-primary mb-2" role="status" style="width: 2.2rem; height: 2.2rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span class="text-muted fw-semibold small loader-status-text">{{ $targetLoaderText }}</span>
    </div>
</div>

<!-- SweetAlert2 (Swal) Flash Message & Helper Integration -->
@if(!empty($_SESSION['success']) || !empty($_SESSION['error']) || !empty($_SESSION['status']) || !empty($_SESSION['info']))
    @php
        $swalType = !empty($_SESSION['error']) ? 'error' : (!empty($_SESSION['warning']) ? 'warning' : (!empty($_SESSION['info']) ? 'info' : 'success'));
        $swalTitle = !empty($_SESSION['error']) ? 'Error' : (!empty($_SESSION['warning']) ? 'Notice' : (!empty($_SESSION['info']) ? 'Information' : 'Success'));
        $swalMsg = (string)($_SESSION['error'] ?? $_SESSION['success'] ?? $_SESSION['status'] ?? $_SESSION['info'] ?? '');
        $swalJson = json_encode($swalMsg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '""';
        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['status'], $_SESSION['warning'], $_SESSION['info']);
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rawMsg = {!! $swalJson !!};
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: '{{ $swalType }}',
                    title: '{{ $swalTitle }}',
                    text: rawMsg,
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonColor: '#4f46e5'
                });
            } else if (typeof window.showNotification === 'function') {
                window.showNotification('{{ $targetNotificationId }}', '{{ $swalType }}', rawMsg);
            }
        });
    </script>
@endif

<script>
    // Universal Loader & Notification Helpers
    if (typeof window !== 'undefined') {
        window.toggleAppLoader = function(show = true, message = null, loaderId = '{{ $targetLoaderId }}') {
            const loaderEl = document.getElementById(loaderId);
            if (!loaderEl) return;
            loaderEl.style.display = show ? 'block' : 'none';
            if (message) {
                const textEl = loaderEl.querySelector('.loader-status-text');
                if (textEl) textEl.textContent = message;
            }
        };

        window.showAppSwal = function(title, text, icon = 'info') {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert(title + ': ' + text);
            }
        };

        window.showAppToast = function(message, icon = 'success') {
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                return Toast.fire({ icon: icon, title: message });
            } else {
                alert(message);
            }
        };
    }
</script>
