// --- LOGIN --- //

function validarLogin() {
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  const email = emailInput.value.trim();
  const pass = passwordInput.value.trim();

  let emailError = document.getElementById("email-error-login");
  let passwordError = document.getElementById("password-error-login");

  if (!emailError) {
    emailError = document.createElement("p");
    emailError.id = "email-error-login";
    emailError.className =
      "mt-1 text-sm text-red-600 bg-red-100 rounded-md px-3 py-1 w-72 text-left"; // Afegida text-left
    emailInput.parentNode.appendChild(emailError);
  }
  if (!passwordError) {
    passwordError = document.createElement("p");
    passwordError.id = "password-error-login";
    passwordError.className =
      "mt-1 text-sm text-red-600 bg-red-100 rounded-md px-3 py-1 w-72 text-left"; // Afegida text-left
    passwordInput.parentNode.appendChild(passwordError);
  }

  emailError.textContent = "";
  passwordError.textContent = "";

  let valid = true;

  if (email === "") {
    emailError.textContent = "El correu electrònic no pot estar buit.";
    emailInput.classList.add("border-red-600");
    emailInput.classList.remove("border-green-600");
    valid = false;
  } else {
    const emailRegex = /^[a-zA-Z0-9.]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!emailRegex.test(email)) {
      emailError.textContent = "Correu invàlid.";
      emailInput.classList.add("border-red-600");
      emailInput.classList.remove("border-green-600");
      valid = false;
    } else {
      emailInput.classList.remove("border-red-600");
      emailInput.classList.add("border-green-600");
    }
  }

  if (pass === "") {
    passwordError.textContent = "La contrasenya no pot estar buida.";
    passwordInput.classList.add("border-red-600");
    passwordInput.classList.remove("border-green-600");
    valid = false;
  } else {
    passwordInput.classList.remove("border-red-600");
    passwordInput.classList.add("border-green-600");
  }

  if (valid) {
    window.location.href = "main.html";
    return false;
  }

  return false;
}

function mostrarRequisitosContrasenya() {
  const pass = document.getElementById("password").value;
  const requisitos = document.getElementById("password-requisitos");

  if (requisitos) {
    requisitos.style.display = pass.length === 0 ? "none" : "block";
    document.getElementById("req-length").style.color = pass.length >= 8 ? "green" : "red";
    document.getElementById("req-mayus").style.color = /[A-Z]/.test(pass) ? "green" : "red";
    document.getElementById("req-minus").style.color = /[a-z]/.test(pass) ? "green" : "red";
    document.getElementById("req-num").style.color = /\d/.test(pass) ? "green" : "red";
    document.getElementById("req-especial").style.color = /[^A-Za-z0-9]/.test(pass) ? "green" : "red";
  }
}

// --- REGISTER --- //

function validarEmailRegister() {
  const emailInput = document.getElementById("email");
  const emailError = document.getElementById("email-error");
  const email = emailInput.value.trim();

  const regexEmail = /^[a-zA-Z0-9.]+@[^\s@]+\.[^\s@]+$/;

  if (email === "") {
    emailError.style.display = "none";
    emailInput.style.borderColor = "black";
    return true;
  }

  if (!regexEmail.test(email)) {
    emailError.textContent = "El correu electrònic no és vàlid.";
    emailError.style.display = "block";
    emailError.style.color = "red";
    emailError.style.textAlign = "left";
    emailError.style.paddingLeft = "0.5rem";
    emailInput.style.borderColor = "red";
    return false;
  } else {
    emailError.style.display = "none";
    emailInput.style.borderColor = "black";
    return true;
  }
}

function validarFormularioRegister(e) {
  e.preventDefault();

  const emailInput = document.getElementById("email");
  const emailError = document.getElementById("email-error");
  const passwordInput = document.getElementById("password");
  const passwordRequisitos = document.getElementById("password-requisitos");

  const email = emailInput.value.trim();
  const pass = passwordInput.value.trim();

  const regexEmail = /^[a-zA-Z0-9.]+@[^\s@]+\.[^\s@]+$/;
  const regexPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

  let valid = true;

  if (!regexEmail.test(email)) {
    emailError.textContent = "Introdueix un correu vàlid.";
    emailError.style.display = "block";
    emailError.style.color = "red";
    emailError.style.textAlign = "left";
    emailError.style.paddingLeft = "0.5rem";
    emailInput.style.borderColor = "red";
    emailInput.focus();
    valid = false;
  } else {
    emailError.style.display = "none";
    emailInput.style.borderColor = "black";
  }

  if (!regexPass.test(pass)) {
    passwordRequisitos.style.display = "block";
    passwordInput.style.borderColor = "red";
    valid = false;
  } else {
    passwordRequisitos.style.display = "none";
    passwordInput.style.borderColor = "black";
  }

  if (valid) document.getElementById("form").submit();
}
