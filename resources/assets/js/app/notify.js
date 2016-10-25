module.exports = {
    ajaxError: function (title, errorResponse) {
        if (errorResponse.status == 401) { // Unauthorized
            window.location.reload();
            return;
        }

        new PNotify({
            type: 'error',
            title: title,
            text: '#' + errorResponse.status + ': ' + (errorResponse.status == 0 ?
                'Network error' : errorResponse.statusText)
        });
    }
};