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