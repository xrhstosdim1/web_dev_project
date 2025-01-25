// *** FORGOT PASSWORD ***\\
document.getElementById('forgot-password-button').addEventListener('click', function (e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();

    if (!email) {
        showNotification('Παρακαλώ εισάγετε το email σας για να ανακτήσετε τον κωδικό.', 'error');
        return;
    }

    fetch('forgot_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const centralNotification = document.createElement('div');
            centralNotification.className = 'central-notification';
            centralNotification.textContent = `Ο κωδικός σας είναι: ${data.password}`;
            document.body.appendChild(centralNotification);

            setTimeout(() => {
                centralNotification.remove();
            }, 5000);
        } else {
            showNotification(data.message || "Προέκυψε σφάλμα κατά την ανάκτηση του κωδικού.", 'error');
        }
    })
    .catch(error => {
        console.error("Error fetching password:", error);
        showNotification("Υπήρξε σφάλμα κατά την ανάκτηση του κωδικού. Δοκιμάστε ξανά.", 'error');
    });
});

// *** TOGGLE PASS FIELD ***\\
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;

    const toggleIcon = document.getElementById("toggleIcon");

    // Νέα εικονίδια
    toggleIcon.className = type === "password" 
        ? "fas fa-eye" // Εικονίδιο για απόκρυψη
        : "fas fa-eye-slash"; // Εικονίδιο για εμφάνιση

    // Εφέ περιστροφής για περισσότερη "ζωντάνια"
    toggleIcon.style.transition = "transform 0.3s ease";
    toggleIcon.style.transform = type === "password" ? "rotate(0deg)" : "rotate(180deg)";
});