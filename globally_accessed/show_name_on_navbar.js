document.addEventListener("DOMContentLoaded", function () {
    const userNameElement = document.getElementById('user-name');
    
    if (userNameElement) {
        fetch('../api/get_logged_in_users_Fname.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userName = `${data.data.name} ${data.data.surname}`;
                    userNameElement.textContent = userName;
                } else {
                    console.error('Failed to fetch user details:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching user details:', error);
            });
    } else {
        console.warn('User name element not found in the DOM.');
    }
});