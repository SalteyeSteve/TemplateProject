$(document).ready(function () {

    executeWeatherRequest();
    // simple ajax get action
    $('.selector').click(function () {
        let id = $(this).data('id');

        $.ajax({
            method: "POST",
            url: 'ajax/genericAjaxHandler.php',
            data: {id: id, action: 'getData'},
            success: function (json) {
                console.log(json);
                let obj = JSON.parse(json);
                // window.location.reload(); for page refresh
                // let title = obj[0]['id'];

            }
        });
    });

    function executeWeatherRequest() {
        // ajax continuous api call
        let id = $(this).data('id'),
            // 10 seconds interval
            interval = 10000,
            requestPending = false;

        // requests weather once
        $.ajax({
            method: "POST",
            url: 'ajax/genericAjaxHandler.php',
            data: {id: id, action: 'getWeather'},
            success: function (json) {
               $('body').append(json);
            }
        });

        // continuous call
        // setInterval(function () {
        //     if (requestPending) return;
        //
        //     $.ajax({
        //         method: "POST",
        //         url: 'ajax/genericAjaxHandler.php',
        //         data: {id: id, action: 'getWeather'},
        //         success: function (json) {
        //             console.log('Executing weather request');
        //             console.log(json);
        //             // let obj = JSON.parse(json);
        //             requestPending = false;
        //         }
        //     });
        //
        //     requestPending = true;
        // }, interval);

    }
});

