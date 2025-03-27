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


