document.addEventListener('DOMContentLoaded', () => {
	fetchCompletedTheses();
	loadAnnouncements();
});

function fetchCompletedTheses() {
	const tableBody = document.getElementById('completed-table-body');
	const loadingIndicator = document.getElementById('loading');

	if (!tableBody) {
		showNotification('Λείπει το completed-table-body.', 'error');
        console.error('Element completed-table-body not found.');
		return;
	}

	loadingIndicator.style.display = 'block';

	fetch('../api/get_completed_theses.php')
		.then(response => response.json())
		.then(data => {
			loadingIndicator.style.display = 'none';
			tableBody.innerHTML = '';

			if (data.success && data.theses.length > 0) {
				data.theses.forEach(thesis => {
					const formattedDate = new Date(thesis.submission_date).toLocaleDateString('el-GR');

					const row = `
                        <tr>
                            <td>${thesis.topic}</td>
                            <td>${thesis.professor_name}</td>
                            <td>${thesis.student_name}</td>
                            <td>${formattedDate}</td>
                            <td><a href="${thesis.nemertes_link}" target="_blank" class="btn btn-link">Νημερτής</a></td>
                        </tr>
                    `;
					tableBody.insertAdjacentHTML('beforeend', row);
				});
			} else {
				tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">Δεν βρέθηκαν ολοκληρωμένες διπλωματικές προς προβολή.</td>
                    </tr>
                `;
			}
		})
		.catch(error => {
			console.error('Σφάλμα κατά τη φόρτωση των ολοκληρωμένων διπλωματικών:', error);
			loadingIndicator.style.display = 'none';
			tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger">Σφάλμα κατά τη φόρτωση των δεδομένων.</td>
                </tr>
            `;
		});
}

function loadAnnouncements() {
    const announcementsTableBody = document.getElementById('announcements-table-body');
    const loadingAnnouncements = document.getElementById('loading-announcements');

    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;

    if (!announcementsTableBody) {
        showNotification('Λείπει το announcements-table-body.', 'error');
        console.error('Element announcements-table-body not found.');
        return;
    }

    loadingAnnouncements.style.display = 'block';

    const queryParams = new URLSearchParams();
    if (startDate) queryParams.append('start_date', startDate);
    if (endDate) queryParams.append('end_date', endDate);

    fetch(`../api/get_announcments.php?${queryParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            loadingAnnouncements.style.display = 'none';
            announcementsTableBody.innerHTML = '';

            if (data.success && data.announcements.length > 0) {
                window.announcementsData = data.announcements;

                data.announcements.forEach(announcement => {
                    const examDate = new Date(announcement.exam_date);
                    const formattedDate = examDate.toLocaleDateString('el-GR');//telika mporei na ginei pio eukola to format, oops
                    const formattedTime = examDate.toLocaleTimeString('el-GR', { hour: '2-digit', minute: '2-digit', hour12: false });

                    const row = `
                        <tr>
                            <td>${announcement.student_name}</td>
                            <td>${announcement.thesis_topic}</td>
                            <td>${formattedDate}, ${formattedTime}</td>
                            <td>${announcement._location}</td>
                            <td>${announcement.ann_body}</td>
                        </tr>
                    `;
                    announcementsTableBody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                window.announcementsData = [];
                announcementsTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">Δεν βρέθηκαν ανακοινώσεις.</td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά τη φόρτωση των ανακοινώσεων:', error);
            announcementsTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">Σφάλμα κατά τη φόρτωση των ανακοινώσεων.</td>
                </tr>
            `;
        });
}

function exportToJSON() {
    if (!window.announcementsData || window.announcementsData.length === 0) {
        showNotification('Δεν υπάρχουν ανακοινώσεις προς εξαγωγή.', 'error');
        return;
    }

    const jsonBlob = new Blob([JSON.stringify(window.announcementsData, null, 2)], { type: 'application/json' });
    const jsonUrl = URL.createObjectURL(jsonBlob);

    const downloadLink = document.createElement('a');
    downloadLink.href = jsonUrl;
    downloadLink.download = 'announcements.json';
    downloadLink.click();
    URL.revokeObjectURL(jsonUrl);
}

function exportToXML() {
    if (!window.announcementsData || window.announcementsData.length === 0) {
        showNotification('Δεν υπάρχουν ανακοινώσεις προς εξαγωγή.', 'error');
        return;
    }

    const xmlData = window.announcementsData.map(announcement => `
        <announcement>
            <student_name>${announcement.student_name}</student_name>
            <thesis_topic>${announcement.thesis_topic}</thesis_topic>
            <exam_date>${announcement.exam_date}</exam_date>
            <location>${announcement._location}</location>
            <ann_body>${announcement.ann_body}</ann_body>
        </announcement>
    `).join('');

    const xmlBlob = new Blob([`<announcements>${xmlData}</announcements>`], { type: 'application/xml' });
    const xmlUrl = URL.createObjectURL(xmlBlob);

    const downloadLink = document.createElement('a');
    downloadLink.href = xmlUrl;
    downloadLink.download = 'announcements.xml';
    downloadLink.click();
    URL.revokeObjectURL(xmlUrl);
}

//event listeners gia export
document.getElementById('export-json').addEventListener('click', exportToJSON);
document.getElementById('export-xml').addEventListener('click', exportToXML);

//event listeners gia hmeromhnies filtra
document.getElementById('start-date').addEventListener('change', loadAnnouncements);
document.getElementById('end-date').addEventListener('change', loadAnnouncements);
