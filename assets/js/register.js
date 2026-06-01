document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('registerForm');

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('konfirmasi_password');

    const passwordError = document.getElementById('passwordError');

    document.getElementById('togglePassword')
        .addEventListener('click', function () {

        const icon = this.querySelector('i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }

    });

    document.getElementById('toggleConfirmPassword')
        .addEventListener('click', function () {

        const icon = this.querySelector('i');

        if (confirmPassword.type === 'password') {
            confirmPassword.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            confirmPassword.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }

    });

    form.addEventListener('submit', function (e) {

        password.classList.remove('is-invalid');
        confirmPassword.classList.remove('is-invalid');

        if (password.value !== confirmPassword.value) {

            e.preventDefault();

            password.classList.add('is-invalid');
            confirmPassword.classList.add('is-invalid');

            passwordError.classList.remove('d-none');
            passwordError.textContent =
                'Password dan konfirmasi password tidak sama.';

            confirmPassword.focus();

            return false;
        }

        passwordError.classList.add('d-none');

    });

});