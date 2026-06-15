document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('resetPasswordForm');

    if (!form) {
        return;
    }

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('konfirmasi_password');
    const passwordError = document.getElementById('passwordError');

    const togglePassword = (button, input) => {
        button.addEventListener('click', function () {
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    };

    togglePassword(document.getElementById('togglePassword'), password);
    togglePassword(document.getElementById('toggleConfirmPassword'), confirmPassword);

    form.addEventListener('submit', function (event) {
        password.classList.remove('is-invalid');
        confirmPassword.classList.remove('is-invalid');

        if (password.value !== confirmPassword.value) {
            event.preventDefault();

            password.classList.add('is-invalid');
            confirmPassword.classList.add('is-invalid');
            passwordError.classList.remove('d-none');
            passwordError.textContent = 'Password dan konfirmasi password tidak sama.';
            confirmPassword.focus();

            return false;
        }

        passwordError.classList.add('d-none');
    });

});
