function showNotification(message, type = 'error') {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span class="message">${message}</span>
    `;
    container.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}


const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('error');
const email = urlParams.get('email');

if (error) {
    let message;
    let info;
    if (error === 'invalid_credentials') {
        message = 'Λάθος κωδικός πρόσβασης';
        if (email) document.getElementById('email').value = email;
        showNotification(message, 'error');
    } else if (error === 'user_not_found') {
        message = 'Το email δεν υπάρχει στο σύστημα';
        showNotification(message, 'error');
    } else if (error == 'access-denied'){
        message = 'Μη εξουσιοδοτημένη πρόσβαση.';
        showNotification(message, 'error');
    }else if (error == 'not-logged-in'){
        message = 'Προσπαθείτε να δείτε περιοεχόμενο που προυποθέτει σύνδεση. Παρακαλώ συνδεθείτε';
        showNotification(message, 'error');
    }else if (error == 'logged-out'){
        info = 'Αποσυνδεθήκατε';
        showNotification(info, 'info');
    } else if (error == 'thesis-published'){
        info = 'Το θέμα της εργασίας δημοσιεύθηκε';
        showNotification(info, 'info');
    }else if (error = 'error-publishing-thesis'){
        message = 'Σφάλμα κατά τη δημοσίευση της εργασίας';
        showNotification(message, 'error');
    }else{
        message = 'Άγνωστο σφάλμα';
        showNotification(message, 'error');
    };
    
}