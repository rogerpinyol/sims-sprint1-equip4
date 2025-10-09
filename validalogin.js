function validarLogin() {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    const email = emailInput.value.trim();
    const pass = passwordInput.value.trim();

    let emailError = document.getElementById("email-error-login");
    let passwordError = document.getElementById("password-error-login");

    // Si no existeixen els missatges d'error, els creem
    if (!emailError) {
        emailError = document.createElement("p");
        emailError.id = "email-error-login";
        emailError.className =
            "mt-1 text-sm text-red-600 bg-red-100 rounded-md px-3 py-1 w-72 text-left";
        emailInput.parentNode.appendChild(emailError);
    }
    if (!passwordError) {
        passwordError = document.createElement("p");
        passwordError.id = "password-error-login";
        passwordError.className =
            "mt-1 text-sm text-red-600 bg-red-100 rounded-md px-3 py-1 w-72 text-left";
        passwordInput.parentNode.appendChild(passwordError);
    }

    emailError.textContent = "";
    passwordError.textContent = "";

    let valid = true;

    // Validem l'email
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

    // Validem la contrasenya
    if (pass === "") {
        passwordError.textContent = "La contrasenya no pot estar buida.";
        passwordInput.classList.add("border-red-600");
        passwordInput.classList.remove("border-green-600");
        valid = false;
    } else {
        passwordInput.classList.remove("border-red-600");
        passwordInput.classList.add("border-green-600");
    }

    // Si les dades són vàlides, enviem la petició AJAX
    if (valid) {
        const formData = new FormData();
        formData.append("email", email);
        formData.append("password", pass);

        fetch("login.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirigir a la pàgina principal
                window.location.href = "main.html";
            } else {
                // Mostrar l'error rebut del servidor dins del formulari
                passwordError.textContent = data.error || 'Error desconegut';
                passwordInput.classList.add("border-red-600");
                passwordInput.classList.remove("border-green-600");
            }
        })
        .catch(error => {
            console.error('Error en la connexió:', error);
            passwordError.textContent = "Error en la connexió.";
        });

        return false; // Evitem l'enviament normal del formulari
    }

    return false; // Si no és vàlid, el formulari no s'envia
}
