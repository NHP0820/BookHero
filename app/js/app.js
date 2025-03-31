/*Show password*/
$(document).ready(function () {
    $("#togglePassword").click(function () {
        let passwordField = $("#password");
        let icon = $(this).find("i");

        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            passwordField.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });
}); 

/*Show Confirm password*/
$(document).ready(function () {
    $("#togglePassword2").click(function () {
        let passwordField = $("#confirmPassword");
        let icon = $(this).find("i");

        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            passwordField.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });
});

//show page loading
$(document).ready(function () {
    $("#registerButton").click(function (event) {
        event.preventDefault();
        $("#loadingText").show();
        $(this).prop("disabled", true);

        setTimeout(() => {
            $(".form").submit();
        }, 1000);
    });
});

//resend verification email
$(document).ready(function() {
    $("#resendVerification").on("click", function(event) {
        event.preventDefault();
        var email = $(this).data("email");

        $.ajax({
            url: "resendVerification.php",
            type: "POST",
            data: { email: email },
            dataType: "json",
            beforeSend: function() {
                $("#resendVerification").text("Sending...").css("color", "black");
            },
            success: function(response) {
                if (response.status === "success") {
                    window.location.href = 'login.php';
                } else {
                    window.location.href = 'login.php';
                }
            }
        });
    })
});

$(document).ready(function() {
    $("#forgetPassword").on("click", function(event) {
        event.preventDefault();

        var email = $("#email").val().trim();

        if (email === '') {
            $(".err").remove();
            $("#email").after("<span class='err' style='color:red;'>Required</span>");
            return;
        }
        $.ajax({
            url: "forgetPassword.php",
            type: "POST",
            data: { email: email },
            dataType: "json",
            beforeSend: function() {
                $("#forgetPassword").text("Sending...").css("color", "black");
            },
            success: function(response) {
                if (response.status === "success") {
                    window.location.href = 'login.php';
                } else {
                    window.location.href = 'login.php';
                }
            },
        });

    });
});

$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();
    
    // Confirmation message
    $('[data-confirm]').on('click', e => {
        const text = e.target.dataset.confirm || 'Are you sure?';
        if (!confirm(text)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

    // Auto uppercase
    $('[data-upper]').on('input', e => {
        const a = e.target.selectionStart;
        const b = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(a, b);
    });

    // Photo preview
    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];
        const img = $(e.target).siblings('img')[0];

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        }
        else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });

});

// Autofocus
$('form :input:not(button):first').focus();
$('.err:first').prev().focus();
$('.err:first').prev().find(':input:first').focus();