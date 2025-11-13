document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('login-form');
  if (!form) return;
  var email = form.querySelector('input[name="email"]');
  var pwd = form.querySelector('input[name="password"]');
  var emailErr = document.getElementById('email-error');
  var pwdErr = document.getElementById('password-error');

  function validateEmail() {
    if (!email.value || !email.checkValidity()) {
      emailErr.textContent = 'Enter a valid email address.';
      emailErr.classList.remove('hidden');
      return false;
    } else {
      emailErr.textContent = '';
      emailErr.classList.add('hidden');
      return true;
    }
  }

  function validatePassword() {
    if (!pwd.value) {
      pwdErr.textContent = 'Password is required.';
      pwdErr.classList.remove('hidden');
      return false;
    } else {
      pwdErr.textContent = '';
      pwdErr.classList.add('hidden');
      return true;
    }
  }

  email.addEventListener('input', validateEmail);
  email.addEventListener('blur', validateEmail);
  pwd.addEventListener('input', validatePassword);
  pwd.addEventListener('blur', validatePassword);

  form.addEventListener('submit', function(e) {
    var valid = validateEmail() & validatePassword();
    if (!valid) e.preventDefault();
  });
});