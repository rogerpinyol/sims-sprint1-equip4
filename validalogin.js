function validarLogin() {
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  const email = emailInput.value.trim();
  const pass = passwordInput.value.trim();

  let emailError = document.getElementById("email-error-login");
  let passwordError = document.getElementById("password-error-login");

  // Crear i posar classes Tailwind als errors si no existeixen
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
