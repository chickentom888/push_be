$(document).ready(function () {
    toastrInit();
});
function copyTextToClipboard() {
    var clipboard = new ClipboardJS('.btn-copy');
    clipboard.on('success', function (e) {
        toastr.success('Copied!');
    });
    clipboard.on('error', function (e) {
        toastr.error('Copy fail');
    });
}
function toastrInit() {
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "2000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
}
