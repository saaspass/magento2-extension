document.getElementById("otp").setAttribute("data-validate", "{required:false}");
require(["jquery"], function ($) {
    var poll = function () {
        $.ajax({
            url : '/rest/V1/saaspass/authenticated/session/' + document.getElementById("session").value,
            type: 'GET',
            success: function (data) {
                if (data === 'ready') {
                    location = '';
                }

            },
            error: function () {

            },
        });

    };

    poll();
    setInterval(function () {
        poll();

    },2000);

})();