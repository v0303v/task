$(document).ready(function () {
    var form = $(this).find('form[name="form_registration"]');

    function ajaxReq() {

        $('#submitBtn').on('click', function (e) {
            e.preventDefault();

            var first_name = $('#first_name').val();
            var last_name = $('#last_name').val();
            var age = $('#age').val();
            var gender = $('#gender').val();
            var phone_number = $('#phone_number').val();
            var email = $('#email').val();
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                contentType: 'application/json',
                type: 'JSON',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: JSON.stringify({
                    "first_name": first_name,
                    "last_name": last_name,
                    "age": age,
                    "gender": gender,
                    "phone_number": phone_number,
                    "email": email
                }),
                success: function (data) {
                    alert(data)
                },
                error: function (data) {
                    alert(data)
                }
            });
        });
    }


    ajaxReq();
});
