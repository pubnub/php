$(function () {
    $("#publish").on('click', function () {
        $.get("/publish.php", {
            message: $("#message-input").val(),
            cipher_key: $("#cipher-input").val(),
            ssl: $("#ssl").is(":checked")
        }, function (data) {
            console.log(data);
        });
    });
});