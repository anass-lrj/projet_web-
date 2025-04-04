document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll("form[data-validate='true']");

    forms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            if (!validateForm(form)) {
                event.preventDefault(); // Empêche l'envoi du formulaire si la validation échoue
            }
        });
    });
});

/**
 * Valide un formulaire donné.
 * @param {HTMLFormElement} form - Le formulaire à valider.
 * @returns {boolean} - Retourne true si le formulaire est valide, sinon false.
 */
function validateForm(form) {
    let isValid = true;
    const errors = [];

    // Valider les champs texte (nom, prénom, etc.)
    form.querySelectorAll("input[type='text'], input[type='email'], input[type='password']").forEach((input) => {
        const value = input.value.trim();
        const fieldName = input.getAttribute("name") || "Ce champ";

        if (input.hasAttribute("required") && value === "") {
            errors.push(`${fieldName} est requis.`);
            isValid = false;
        }

        if (input.hasAttribute("minlength") && value.length < parseInt(input.getAttribute("minlength"))) {
            errors.push(`${fieldName} doit contenir au moins ${input.getAttribute("minlength")} caractères.`);
            isValid = false;
        }

        if (input.hasAttribute("maxlength") && value.length > parseInt(input.getAttribute("maxlength"))) {
            errors.push(`${fieldName} ne doit pas dépasser ${input.getAttribute("maxlength")} caractères.`);
            isValid = false;
        }

        if (input.type === "email" && value !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            errors.push(`${fieldName} doit être une adresse email valide.`);
            isValid = false;
        }
    });

    // Valider les champs select (rôle, etc.)
    form.querySelectorAll("select[required]").forEach((select) => {
        if (select.value === "") {
            const fieldName = select.getAttribute("name") || "Ce champ";
            errors.push(`${fieldName} est requis.`);
            isValid = false;
        }
    });

    // Afficher les erreurs
    const errorContainer = form.querySelector(".form-errors");
    if (errorContainer) {
        errorContainer.innerHTML = "";
        if (errors.length > 0) {
            errors.forEach((error) => {
                const errorElement = document.createElement("li");
                errorElement.textContent = error;
                errorContainer.appendChild(errorElement);
            });
            errorContainer.style.display = "block";
        } else {
            errorContainer.style.display = "none";
        }
    }

    return isValid;
}