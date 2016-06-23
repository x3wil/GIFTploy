setInterval(function () {
    window.scrollTo(0, 999999);
}, 100);

window.onload = function() {
    var urlAfter = parent.$('#process-console-iframe').data('url-after');

    if (typeof urlAfter !== 'undefined') {
        setTimeout(function () {
            parent.window.location.href = urlAfter;
        }, 1500)
    }
};