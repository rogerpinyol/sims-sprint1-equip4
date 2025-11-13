document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('register-form');
  if (!form) return;
  var name = form.querySelector('input[name="name"]');
  var email = form.querySelector('input[name="email"]');
  var pwd = form.querySelector('input[name="password"]');
  var nameErr = document.getElementById('name-error');
  var emailErr = document.getElementById('email-error');
  var pwdErr = document.getElementById('password-error');

  function validateName() {
    var nameVal = name.value.trim();
    if (!nameVal || nameVal.length < 2) {
      nameErr.textContent = 'Name must be at least 2 characters.';
      nameErr.classList.remove('hidden');
      return false;
    } else if (!/^[A-Za-zÀ-ÿ ]+$/.test(nameVal)) {
      nameErr.textContent = 'Name must contain only letters and spaces.';
      nameErr.classList.remove('hidden');
      return false;
    } else {
      nameErr.textContent = '';
      nameErr.classList.add('hidden');
      return true;
    }
  }

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
    var pwdVal = pwd.value;
    if (!pwdVal || pwdVal.length < 8
      || !/[A-Z]/.test(pwdVal)
      || !/[a-z]/.test(pwdVal)
      || !/\d/.test(pwdVal)
      || !/[^A-Za-z\d]/.test(pwdVal)) {
      pwdErr.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
      pwdErr.classList.remove('hidden');
      return false;
    } else {
      pwdErr.textContent = '';
      pwdErr.classList.add('hidden');
      return true;
    }
  }

  name.addEventListener('input', validateName);
  name.addEventListener('blur', validateName);
  email.addEventListener('input', validateEmail);
  email.addEventListener('blur', validateEmail);
  pwd.addEventListener('input', validatePassword);
  pwd.addEventListener('blur', validatePassword);

  form.addEventListener('submit', function(e) {
    var valid = validateName() & validateEmail() & validatePassword();
    if (!valid) e.preventDefault();
  });
});