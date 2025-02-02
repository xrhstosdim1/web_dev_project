document.addEventListener('DOMContentLoaded', function() {
    setupMenuNavigation();
    setupFilters();
    setupCommentForm();
    setupStudentSearch('student-search', 'student-suggestions', 'student-am');
    setupStudentSearch('edit-student-search', 'edit-student-suggestions', 'edit-student-am');
    setupPublishForm();
    

    //clear ola sto modal
    const thesisModal = document.getElementById('thesisModal');
    if (thesisModal) {
        thesisModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('exetasi-content').style.display = 'none';
            document.getElementById('vathmologio').style.display = 'none';

            resetProgressModal();

            document.getElementById('thesis-actions').innerHTML = '';
            document.getElementById('thesis-comments').innerHTML = '';

            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.reset();
            }

            document.getElementById('status-info').textContent = '-';
            document.getElementById('thesis-topic-header').textContent = '';
            document.getElementById('thesis-summary').textContent = '';
            document.getElementById('thesis-student').textContent = '';
            document.getElementById('thesis-supervisor').textContent = '';
            document.getElementById('member2').textContent = '';
            document.getElementById('member3').textContent = '';
            document.getElementById('thesis-file-link').innerHTML = '';

            document.getElementById('epivlepon-crit1').disabled = true;
            document.getElementById('epivlepon-crit2').disabled = true;
            document.getElementById('epivlepon-crit3').disabled = true;
            document.getElementById('epivlepon-crit4').disabled = true;
            document.getElementById('member2-crit1').disabled = true;
            document.getElementById('member2-crit2').disabled = true;
            document.getElementById('member2-crit3').disabled = true;
            document.getElementById('member2-crit4').disabled = true;
            document.getElementById('member3-crit1').disabled = true;
            document.getElementById('member3-crit2').disabled = true;
            document.getElementById('member3-crit3').disabled = true;
            document.getElementById('member3-crit4').disabled = true;

            const cancelContainer = document.getElementById('cancel-assignment-container');
            if (cancelContainer) {
                cancelContainer.innerHTML = '';
            }
        });
    }
});
//TODO:: prepei na mpei kai sth grammateia kati tetoio
function cloneAndAddListener(elementId, listenerFn) {
    const oldElem = document.getElementById(elementId);
    if (!oldElem) return;

    const newElem = oldElem.cloneNode(true);
    oldElem.replaceWith(newElem);

    newElem.addEventListener('click', listenerFn);
}

// *** MENU NAVIGATION *** \\
function setupMenuNavigation() {
    document.querySelectorAll('.nav-link, .dropdown-item').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            const section = this.getAttribute('data-section');
            if (!section) return;

            document.querySelectorAll('.section-container').forEach(container => {
                container.style.display = 'none';
                container.classList.remove('active');
            });

            const targetSection = document.getElementById(section);
            if (targetSection) {
                targetSection.style.display = 'block';
                setTimeout(() => targetSection.classList.add('active'), 10);
            } else {
                console.error(`Section with ID '${section}' not found.`);
            }

            switch (section) {
                case 'my-theses':
                    loadMyTheses();
                    break;
                case 'invitations':
                    loadProfessorRequests();
                    break;
                case 'assign':
                    break;
                case 'edit-topics':
                    loadEditTopics();
                    break;
                case 'statistics':
                    loadStatistics();
                    break;
                default:
                    console.warn(`No loader defined for section: ${section}`);
            }
        });
    });

    // Automatically activate the first section on page load
    const initialSection = document.querySelector('.nav-link[data-section], .dropdown-item[data-section]');
    if (initialSection) {
        initialSection.click();
    }
}

// *** FILTER HANDLING *** \\
function setupFilters() {
    const statusFilter = document.getElementById('my-filter-status');
    const roleFilter = document.getElementById('filter-role');

    if (statusFilter && roleFilter) {
        const filterChangeHandler = () => {
            const filterStatus = statusFilter.value;
            const filterRole = roleFilter.value;
            loadMyTheses(filterStatus, filterRole);
        };

        statusFilter.addEventListener('change', filterChangeHandler);
        roleFilter.addEventListener('change', filterChangeHandler);
    }
}

// *** COMMENT SUBMISSION *** \\
//submit gia ta comments
function setupCommentForm() {
    const commentForm = document.getElementById('comment-form');
    const uploadComment = '../api/upload_comment.php';
    if (commentForm) {
        commentForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const submitButton = commentForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            const formData = new FormData(commentForm);
            fetch(uploadComment, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    submitButton.disabled = false;
                    if (data.success) {
                        showNotification('Το σχόλιο αποθηκεύτηκε με επιτυχία.', 'success');
                        commentForm.reset();
                        const thesisId = formData.get('id_diplomatikis');
                        updateCommentsSection(thesisId);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(() => {
                    submitButton.disabled = false;
                    showNotification('Σφάλμα κατά την αποθήκευση του σχολίου.', 'error');
                });
        });
    }
}
//load ta comments
function updateCommentsSection(thesisId) {
    const thesisCommentsUrl = `../api/get_comments_for_a_thesis.php?id_diplomatikis=${thesisId}`;

    fetch(thesisCommentsUrl)
        .then(response => response.json())
        .then(commentsData => {
            const commentsList = document.getElementById('thesis-comments');
            commentsList.innerHTML = '';

            if (commentsData.success && commentsData.requests.length > 0) {
                commentsData.requests.forEach(comment => {
                    const commentCard = document.createElement('div');
                    commentCard.className = 'comment-item';
                    commentCard.innerHTML = `
                        <strong>${comment.date_commented}</strong>
                        <p>${comment.comment}</p>
                    `;
                    commentsList.appendChild(commentCard);
                });
            } else {
                commentsList.innerHTML = '<p class="text-muted">Δεν υπάρχουν σχόλια.</p>';
            }
        })
        .catch(() => {
            showNotification('Σφάλμα κατά τη φόρτωση σχολίων.', 'error');
        });
}

// *** THESIS FUNCTIONS *** \\
//load ta themata tou kathigiti, kai filterng
function loadMyTheses(filterStatus = 'all', filterRole = 'all', currentPage = 1) {
    const thesesPerPage = 7;
    const url = `../api/get_my_theses.php?role=${filterRole}&status=${filterStatus}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('my-theses-table-body');
            const paginationContainer = document.getElementById('pagination-container');
            tableBody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (data.success && data.theses.length > 0) {
                const totalTheses = data.theses.length;
                const totalPages = Math.ceil(totalTheses / thesesPerPage);
                const startIndex = (currentPage - 1) * thesesPerPage;
                const thesesToShow = data.theses.slice(startIndex, startIndex + thesesPerPage);

                thesesToShow.forEach(thesis => {
                    const row = `
                        <tr>
                            <td>${thesis.id}</td>
                            <td>${thesis.topic}</td>
                            <td>${thesis.student || "N/A"}</td>
                            <td>${getStatusText(thesis.status)}</td>
                            <td>${getRoleText(thesis.role)}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="openThesisModal(${thesis.id})">Προβολή</button>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });

                //pagination
                for (let i = 1; i <= totalPages; i++) {
                    const activeClass = i === currentPage ? 'active' : '';
                    const pageItem = `
                        <li class="page-item ${activeClass}">
                            <button class="page-link" onclick="loadMyTheses('${filterStatus}', '${filterRole}', ${i})">
                                ${i}
                            </button>
                        </li>`;
                    paginationContainer.insertAdjacentHTML('beforeend', pageItem);
                }
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">Δεν υπάρχουν διαθέσιμες διπλωματικές για τα επιλεγμένα φίλτρα.</td>
                    </tr>`;
            }
        })
        .catch(error => console.error('Σφάλμα στη φόρτωση των διπλωματικών: ', error));
}
//load to edit section
function loadEditTopics(currentPage = 1) {
    const topicsPerPage = 7;
    const url = `../api/get_my_theses.php?reason=edit`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('topics-table-body');
            const paginationContainer = document.getElementById('pagination-edit-container');

            tableBody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (data.success && data.theses.length > 0) {
                const totalTopics = data.theses.length;
                const totalPages = Math.ceil(totalTopics / topicsPerPage);
                const startIndex = (currentPage - 1) * topicsPerPage;
                const topicsToShow = data.theses.slice(startIndex, startIndex + topicsPerPage);

                topicsToShow.forEach(thesis => {
                    const row = `
                        <tr>
                            <td>${thesis.id}</td>
                            <td>${thesis.topic}</td>
                            <td>${thesis.summary}</td>
                            <td>${getStatusText(thesis.status)}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="openEditModal(${thesis.id})">Επεξεργασία</button>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
                //selides
                for (let i = 1; i <= totalPages; i++) {
                    const activeClass = i === currentPage ? 'active' : '';
                    const pageItem = `
                        <li class="page-item ${activeClass}">
                            <button class="page-link" onclick="loadEditTopics(${i})">${i}</button>
                        </li>`;
                    paginationContainer.insertAdjacentHTML('beforeend', pageItem);
                }
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Δεν υπάρχουν θέματα προς επεξεργασία.</td>
                    </tr>`;
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά τη φόρτωση των θεμάτων:', error);
            showNotification('Σφάλμα κατά τη φόρτωση των θεμάτων.', 'error');
        });
}
//save tis allages apo to edit modal
function saveThesisEdits() {
    const id = document.getElementById('edit-thesis-id').value.trim();
    const topic = document.getElementById('edit-thesis-topic').value.trim();
    const summary = document.getElementById('edit-thesis-summary').value.trim();
    const fileInput = document.getElementById('edit-thesis-file');

    const studentSearchInput = document.getElementById('edit-student-search');
    const studentAmInput = document.getElementById('edit-student-am');
    const currentAm = studentSearchInput.dataset.currentAm || '';
    const newAm = studentAmInput.value.trim();

    if (!id || !topic || !summary) {
        showNotification('Παρακαλώ συμπληρώστε όλα τα πεδία.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('topic', topic);
    formData.append('summary', summary);

    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }

    if (newAm && newAm !== currentAm) {
        formData.append('student_am', newAm);
    }

    fetch('../api/update_thesis.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Η ενημέρωση ήταν επιτυχής.', 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editThesisModal'));
                modal.hide();
                loadEditTopics();
            } else {
                showNotification(data.message || 'Αποτυχία ενημέρωσης.', 'error');
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά την ενημέρωση:', error);
            showNotification('Σφάλμα κατά την ενημέρωση.', 'error');
        });
}
//open to edit modal, kaleitai apo to load edit topics
function openEditModal(thesisId) {
    const thesisDetails = `../api/get_thesis_details.php?id=${thesisId}`;

    fetch(thesisDetails)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const thesis = data.details;

                document.getElementById('edit-thesis-id').value = thesisId;
                document.getElementById('edit-thesis-topic').value = thesis.topic || '';
                document.getElementById('edit-thesis-summary').value = thesis.summary || '';
                const currentFileName = thesis.file_name || 'Δεν έχει οριστεί';
                document.getElementById('current-file-name').innerText = `Τρέχον αρχείο: ${currentFileName}`;

                const studentSearchInput = document.getElementById('edit-student-search');
                const studentAmInput = document.getElementById('edit-student-am');

                if (thesis.student) {
                    studentSearchInput.value = thesis.student;
                    studentAmInput.value = thesis.student_am || '';
                    studentSearchInput.dataset.currentAm = thesis.student_am;
                } else {
                    studentSearchInput.value = '';
                    studentAmInput.value = '';
                    studentSearchInput.dataset.currentAm = '';
                }

                setupStudentSearch('edit-student-search', 'edit-student-suggestions', 'edit-student-am');

                const modal = new bootstrap.Modal(document.getElementById('editThesisModal'));
                modal.show();
            } else {
                showNotification('Αποτυχία φόρτωσης των λεπτομερειών.', 'error');
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά τη φόρτωση των λεπτομερειών:', error);
            showNotification('Σφάλμα κατά τη φόρτωση των λεπτομερειών.', 'error');
        });
}
//vazei to save koumpi
function attachSaveFunction(thesisId) {
    const saveButton = document.querySelector('#editThesisModal .btn-primary');

    saveButton.replaceWith(saveButton.cloneNode(true));

    const newSaveButton = document.querySelector('#editThesisModal .btn-primary');
    newSaveButton.addEventListener('click', function() {
        saveThesisEdits(thesisId);
    });
}
//sunarthsh gia na allazei to rdelo to status bar
function updateSteps(steps, completedIndex, activeIndex) {
    steps.forEach((step, index) => {
        if (index <= completedIndex) {
            step.classList.add('completed');
            step.classList.remove('active', 'clickable');
        } else if (index === activeIndex) {
            step.classList.add('active');
            step.classList.remove('completed', 'clickable');
        } else {
            step.classList.remove('completed', 'active', 'clickable');
        }
    });
}

function resetProgressModal() {
    const steps = document.querySelectorAll('.progress-timeline .step');
    steps.forEach((step) => {
        step.classList.remove('completed', 'active', 'clickable');
        step.style.backgroundColor = '';
    });
}


//open to view modal, kaleitai apo to load my theses
function openThesisModal(thesisId) {
    
    const modalElement = document.getElementById('thesisModal');
    
	const thesisDetailsUrl = `../api/get_thesis_details.php?id=${thesisId}`;
	const thesisRequestsUrl = `../api/get_requests_for_a_thesis.php?id_diplomatikis=${thesisId}`;
	const thesisCommentsUrl = `../api/get_comments_for_a_thesis.php?id_diplomatikis=${thesisId}`;
	const statusExetasiContent = document.getElementById('exetasi-content');
	const statusVathmologoioContent = document.getElementById('vathmologio');


	Promise.all([
			fetch(thesisDetailsUrl).then((response) => response.json()),
			fetch(thesisRequestsUrl).then((response) => response.json()),
			fetch(thesisCommentsUrl).then((response) => response.json()),
		])
		.then(([detailsData, requestsData, commentsData]) => {
			if (detailsData.success) {
				const details = detailsData.details || {};

				document.getElementById('thesis-topic-header').innerText = (details.topic || 'N/A');

				document.getElementById('thesis-summary').innerText = details.summary || 'N/A';
				document.getElementById('thesis-student').innerText = details.student || 'N/A';
				document.getElementById('thesis-supervisor').innerText = details.supervisor || 'N/A';
				document.getElementById('member2').innerText = details.member2 || 'N/A';
				document.getElementById('member3').innerText = details.member3 || 'N/A';
				document.getElementById('thesis-creation-date').innerText = details.creation_date || 'N/A';
				document.getElementById('thesis-selection-date').innerText = details.selection_date || 'N/A';
				document.getElementById('thesis-start-date').innerText = details.start_date || 'N/A';
				document.getElementById('thesis-exam-date').innerText = details.exam_date || 'N/A';
				document.getElementById('thesis-requested-date').innerText = details.thesis_requested || 'N/A';
                document.getElementById('thesis-completion-date').innerText = details.completion_date || 'N/A';

				//arxeio
				const fileName = details.file_name || '';
				const thesisFileLinkElement = document.getElementById('thesis-file-link');

				if (fileName === 'N/A' || fileName === '') {
					thesisFileLinkElement.textContent = 'Δεν υπάρχει αρχείο.';
				} else {
					thesisFileLinkElement.innerHTML = `<a href="../uploads/${fileName}" target="_blank">${fileName}</a>`;
				}


				switch (details.status) {
					case 'diathesimi':
						const stepsDiathesimi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsDiathesimi, 0, 0);
						document.getElementById('status-info').innerText = details.is_supervisor ?
							'Το θέμα σας είναι διαθέσιμο για επιλογή από φοιτητή.' :
							'Το θέμα σας είναι διαθέσιμο για επιλογή από φοιτητή, όμως δεν θα έπρεπε να βλέπετε αυτή τη σελίδα. Επικοινωνήστε με τον διαχειριστή.';
						break;
					case 'pros_anathesi':
						const stepsAnathesi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsAnathesi, 1, 1);
						if (details.is_supervisor) {
							addClickableStep('diathesimi-step', () => updateThesisStatus(thesisId, 'diathesimi'));
							document.getElementById('status-info').innerText = 'Το θέμα σας έχει επιλεχθεί από φοιτητή. Αν δεν επιθυμείτε την ανάθεσή του, πατήστε το κουμπί "Διαθέσιμη". Το θέμα θα γίνει διαθέσιμο για επιλογή.';
						} else {
							document.getElementById('status-info').innerText = 'Το θέμα έχει επιλεχθεί από τον φοιτητή.';
						}
						break;
					case 'pros_egrisi':
						const stepsEgrisi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsEgrisi, 2, 2);
						document.getElementById('status-info').innerText = 'Αναμένεται απόφαση από τη Γενική Συνέλευση του Τμήματος.';
						break;
					case 'energi':
						const stepsEnergi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsEnergi, 3, 3);
						if (details.is_supervisor) {
							addClickableStep('exam-step', () => updateThesisStatus(thesisId, 'exetasi'));
							const statusEnergi = document.getElementById('status-info');
							statusEnergi.style.whiteSpace = 'pre-wrap';
							statusEnergi.textContent =
								'Ο φοιτητής εκπονεί την εργασία του. Μόλις ολοκληρώσει, πατήστε "Εξέταση" για να ορίσει ημερομηνία και τρόπο εξέτασης, όπως επίσης να ανεβάσει και το πρόχειρο κείμενο της εργασίας.\n' +
								'Με το πέρας των 2 ετών από την έναρξη εκπόνησης, θα μπορείτε να ακυρώσετε την ανάθεση ή το θέμα.';

							if (details.two_years_passed) {
								const cancelAssignmentContainer = document.getElementById('cancel-assignment-container');
                                const changeStudentContainer = document.getElementById('change-student-container');

                                changeStudentContainer.innerHTML = '';
								cancelAssignmentContainer.innerHTML = '';

								const cancelAssignmentButton = document.createElement('button');
								cancelAssignmentButton.id = 'cancel-assignment-btn';
								cancelAssignmentButton.className = 'btn btn-danger bt px-4 py-2';
								cancelAssignmentButton.textContent = 'Αίτηση Ακύρωσης Θέματος';
								cancelAssignmentButton.onclick = () => {
                                    showDenyConfirmationModal(
                                        'Επιβεβαίωση Ακύρωσης',
                                        'Είστε σίγουροι ότι θέλετε να αιτηθείτε ακύρωση του θέματος;',
                                        'Το θέμα ΔΕΝ θα είναι διαθέσιμο για επιλογή απο φοιτητή μετά την έγκριση του αιτήματός σας.',
                                        () => cancelAssignment(thesisId)
                                    );
                                };
								cancelAssignmentContainer.appendChild(cancelAssignmentButton);


                                const ChangeStudentButton = document.createElement('button');
								ChangeStudentButton.id = 'change-student-btn';
								ChangeStudentButton.className = 'btn btn-sm btn-danger bt';
								ChangeStudentButton.textContent = 'Αίτηση Αλλαγής Φοιτητή';
								ChangeStudentButton.onclick = () => {
                                    showDenyConfirmationModal(
                                        'Επιβεβαίωση Ακύρωσης',
                                        'Είστε σίγουροι ότι θέλετε να αιτηθείτε αλλαγή φοιτητή;',
                                        'Το θέμα θα ξαναγίνει διαθέσιμο για επιλογή από φοιτητή εάν εγκριθεί το αίτημά σας.',
                                        () => changeStudent(thesisId)
                                    );
                                };
								changeStudentContainer.appendChild(ChangeStudentButton);
							}
						} else {
							document.getElementById('status-info').innerText = 'Ο φοιτητής εκπονεί την εργασία του.';
						}
						break;
					case 'exetasi':
						const stepsExetasi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsExetasi, 4, 4);
						statusExetasiContent.style.display = 'block';
						fetchAndDisplayExamDetails(thesisId);

						const examDateElement = document.getElementById('exam-date');
						examDateElement.innerText = details.exam_date || 'Δεν έχει οριστεί';

						const draftFileElement = document.getElementById('draft-file');
						draftFileElement.innerHTML = details.draft_file ?
							`<a href="../uploads/${details.draft_file}" target="_blank" class="text-decoration-none text-primary">${details.draft_file}</a>` :
							'Δεν έχει αναρτηθεί πρόχειρο κείμενο.';

						const linksContainer = document.getElementById('thesis-links');
						linksContainer.innerHTML = details.links?.length ?
							details.links.map(link => `<li><a href="${link}" target="_blank" class="text-decoration-none text-info">${link}</a></li>`).join('') :
							'<li>Δεν υπάρχουν σύνδεσμοι.</li>';

						if (details.is_supervisor) {
                            addClickableStep('grading-step', () => updateThesisStatus(thesisId, 'vathmologisi'));
                            const statusExetasi = document.getElementById('status-info');
							statusExetasi.style.whiteSpace = 'pre-wrap';
							statusExetasi.textContent =
								'Εφόσον ο φοιτητής έχει εξεταστεί, πατήστε "Βαθμολόγηση" για να επιτρέψετε στους αξιολογητές να βαθμολογήσουν.\n' +
								'Μόλις ο φοιτητής εισάγει ημερομηνία εξέτασης θα φανεί και κάτω από το "Εξέταση".\n' +
								'Δεν μπορείτε να αλλάξετε την κατάσταση προτού περάσει η ημερομηνία εξέτασης και ανακοινώσετε την εξέταση.';
						} else {
							document.getElementById('status-info').innerText = 'Η ημερομηνία εξέτασης φαίνεται και κάτω από το "Εξέταση". Θα μπορείτε να εισάγετε βαθμολογία μόλις η κατάσταση αλλάξει σε "Βαθμολόγηση".';
							document.getElementById('announce-exam-main-btn').style.display = 'none';
						}
						break;
					case 'vathmologisi':
						const stepsVathmologisi = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsVathmologisi, 4, 5);
						document.getElementById('status-info').innerText = 'Εισάγετε τη βαθμολογία σας. Μόλις συμπληρώσουν όλοι οι καθηγητές τη βαθμολογία τους, το βαθμολόγιο θα σταλεί αυτόματα στη γραμματεία για έγκριση.';
						statusExetasiContent.style.display = 'none';
						statusVathmologoioContent.style.display = 'block';
						fetchAndDisplayGrades(thesisId);
						break;

					case 'oloklirwmeni':
						const stepsOloklirwmeni = document.querySelectorAll('.progress-timeline .step');
						resetProgressModal();
						updateSteps(stepsOloklirwmeni, 7, -1);
						document.getElementById('status-info').innerText = details.is_supervisor ?
							'Το θέμα σας έχει ολοκληρωθεί! Παρακάτω φαίνονται όλες οι πληροφορίες του θέματος.' :
							'Η συνδρομή σας στο συγκεκριμένο θέμα έχει ολοκληρωθεί! Παρακάτω φαίνονται όλες οι πληροφορίες του θέματος.';
						break;
                        //TODO:: fix na fainontai vathmlogies, kai pote oloklirwthike h vathmologisi. grab apo to get grades
					case 'akurwmeni':
						document.getElementById('status-info').innerText = 'Το θέμα έχει ακυρωθεί.';
						break;
				}


				//requests
				const actionsList = document.getElementById('thesis-actions');
				actionsList.innerHTML = '';
				if (requestsData.success && requestsData.requests.length > 0) {
					requestsData.requests.forEach((request) => {
						const statusBadge = getRequestStatusBadge(request.status);
						const dateAnswered = request.date_answered ?
							`<small class="text">Απαντήθηκε: ${request.date_answered}</small>` :
							`<small class="text">Απαντήθηκε: -</small>`;

						const requestCard = document.createElement('div');
						requestCard.className = 'request-item mb-3 p-2 border rounded';
						requestCard.innerHTML = `
                            <div>
                                <strong>${request.date_requested}</strong>
                                <p class="mb-1">
                                    Αίτημα από <strong>${request.student_name}</strong> προς <strong>${request.professor_name}</strong>
                                </p>
                                ${dateAnswered}
                            </div>
                            <div>${statusBadge}</div>
                        `;
						actionsList.appendChild(requestCard);
					});
				} else {
					actionsList.innerHTML = '<p class="text-muted">Δεν υπάρχουν αιτήματα.</p>';
				}

				//comments
				const commentsList = document.getElementById('thesis-comments');
				commentsList.innerHTML = '';
				if (commentsData.success && commentsData.requests.length > 0) {
					commentsData.requests.forEach((comment) => {
						const commentCard = document.createElement('div');
						commentCard.className = 'comment-item mb-3 p-2 border rounded';
						commentCard.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <strong>${comment.date_commented}</strong>
                                <span class="text">${comment.prof_email || ''}</span>
                            </div>
                            <p class="mb-0">${comment.comment}</p>
                        `;
						commentsList.appendChild(commentCard);
					});
				} else {
					commentsList.innerHTML = '<p class="text">Δεν υπάρχουν σχόλια.</p>';
				}

				document.getElementById('comment-diplomatikis-id').value = thesisId;
				document.getElementById('comment-prof-email').value = details.supervisor_email || '';

				const modal = new bootstrap.Modal(modalElement);
				modal.show();
			} else {
				showNotification('Σφάλμα κατά τη φόρτωση λεπτομερειών.', 'error');
			}
		})
		.catch(() => {
			showNotification('Σφάλμα κατά τη φόρτωση λεπτομερειών.', 'error');
		});
}
//stelnei aithsh akurwshs ws kathigitis sth grammateia me to tigger insert_grammateia_pros_akurwsh
function cancelAssignment(thesisId) {
    fetch('../api/two_years_cancel.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: thesisId }),
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error('Σφάλμα κατά την ακύρωση της ανάθεσης.');
        }
        return response.json();
    })
    .then((data) => {
        if (data.success) {
            showNotification('Η αίτηση στάλθηκε επιτυχώς.', 'success');
        } else {
            showNotification('Σφάλμα: ' + data.message, 'error');
        }
    })
    .catch((error) => {
        console.error('Σφάλμα:', error);
        showNotification('Υπήρξε σφάλμα κατά την ακύρωση της ανάθεσης.', 'error');
    });
}
//stelnei aithsh allaghs foithth ws kathigitis sth grammateia me trigger insert_grammateia_pros_allagh
function changeStudent(thesisId){
    fetch('../api/change_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: thesisId }),
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error('Σφάλμα κατά την αποστολή της αίτησης.');
        }
        return response.json();
    })
    .then((data) => {
        if (data.success) {
            showNotification('Η αίτηση στάλθηκε επιτυχώς.', 'success');
        } else {
            showNotification('Σφάλμα: ' + data.message, 'error');
        }
    })
    .catch((error) => {
        console.error('Σφάλμα:', error);
        showNotification('Υπήρξε σφάλμα κατά την αποστολή της αίτησης.', 'error');
    });
}
//plhrofories eksetashs fetch kai view. afhnei anakoinwsh mono an einai ola ta pedia gemata
function fetchAndDisplayExamDetails(thesisId) {
    const thesisDetailsUrl = `../api/get_exam_details.php?id_diplwmatikis=${thesisId}`;
    const announceExamMainBtn = document.getElementById("announce-exam-main-btn");

    fetch(thesisDetailsUrl)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                let allFieldsFilled = true;

                const draftFileCell = document.getElementById("draft-file");
                if (data.file_name) {
                    draftFileCell.innerHTML = `
                        <a href="../uploads/${data.file_name}" target="_blank" class="text-primary">
                            ${data.file_name}
                        </a>
                    `;
                } else {
                    draftFileCell.textContent = "Δεν υπάρχει αρχείο.";
                    allFieldsFilled = false;
                }

                const thesisLinksContainer = document.getElementById("thesis-links");
                thesisLinksContainer.innerHTML = "";
                if (data.links.length > 0) {
                    data.links.forEach((link) => {
                        const listItem = document.createElement("li");
                        listItem.innerHTML = `
                            <a href="${link.link}" target="_blank" class="text-primary">${link.link}</a>
                        `;
                        thesisLinksContainer.appendChild(listItem);
                    });
                } else {
                    thesisLinksContainer.innerHTML = '<li class="text-muted">Δεν υπάρχουν σύνδεσμοι.</li>';
                }

                const examDateCell = document.getElementById("exam-date");
                if (data.announcements.length > 0 && data.announcements[0].exam_date) {
                    examDateCell.textContent = new Date(data.announcements[0].exam_date).toLocaleString();
                } else {
                    examDateCell.textContent = "N/A";
                    allFieldsFilled = false;
                }

                const examRoomCell = document.getElementById("exam-room");
                if (data.announcements.length > 0 && data.announcements[0]._location) {
                    examRoomCell.textContent = data.announcements[0]._location;
                } else {
                    examRoomCell.textContent = "N/A";
                    allFieldsFilled = false;
                }


                const examBody = document.getElementById("ann_body_");
                if (data.announcements.length > 0 && data.announcements[0].ann_body) {
                    examBody.textContent = data.announcements[0].ann_body;
                } else {
                    examBody.textContent = "Δεν έχετε ανακοινώσει την εξέταση.";
                }

               

                if (allFieldsFilled) {
                    announceExamMainBtn.disabled = false;

                    announceExamMainBtn.replaceWith(announceExamMainBtn.cloneNode(true));
                    const newAnnounceExamMainBtn = document.getElementById("announce-exam-main-btn");

                    newAnnounceExamMainBtn.addEventListener("click", () => {
                        document.getElementById("announcement").value = "";
                        const modal = new bootstrap.Modal(document.getElementById("examAnnouncementModal"));
                        modal.show();
                        setupAnnouncementForm(thesisId);
                    });
                } else {
                    announceExamMainBtn.disabled = true;
                }

                document.getElementById("exetasi-content").style.display = "block";
            } else {
                showNotification("Δεν βρέθηκαν δεδομένα εξέτασης.", "error");
            }
        })
        .catch((error) => {
            console.error("Σφάλμα κατά την ανάκτηση λεπτομερειών εξέτασης:", error);
            showNotification("Σφάλμα κατά την ανάκτηση λεπτομερειών εξέτασης.", "error");
        });
}
//vathmologio fetch kai view. afhnei edit mono ston sundedemeno
function fetchAndDisplayGrades(thesisId) {
    const thesisGradesUrl = `../api/get_grades.php?id=${thesisId}`;
    const saveGradesBtn = document.getElementById('save-grades-btn');

    fetch(thesisGradesUrl)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                
                const grades = data.data;
                //console.log(grades); //debuggggg
                const allGradesFilled =
                grades.prof1_grade_crit_1 &&
                grades.prof1_grade_crit_2 &&
                grades.prof1_grade_crit_3 &&
                grades.prof1_grade_crit_4 &&
                grades.prof2_grade_crit_1 &&
                grades.prof2_grade_crit_2 &&
                grades.prof2_grade_crit_3 &&
                grades.prof2_grade_crit_4 &&
                grades.prof3_grade_crit_1 &&
                grades.prof3_grade_crit_2 &&
                grades.prof3_grade_crit_3 &&
                grades.prof3_grade_crit_4;

                const loggedInEmail = document.getElementById('vathmologio').getAttribute('data-logged-in-email');
                //emfanish vathmwn
                document.getElementById('vathmologio-epivlepon-onoma').innerText = grades.prof1_name || 'N/A';
                document.getElementById('vathmologio-member2-onoma').innerText = grades.prof2_name || 'N/A';
                document.getElementById('vathmologio-member3-onoma').innerText = grades.prof3_name || 'N/A';

                document.getElementById('epivlepon-crit1').value = grades.prof1_grade_crit_1 || '';
                document.getElementById('epivlepon-crit2').value = grades.prof1_grade_crit_2 || '';
                document.getElementById('epivlepon-crit3').value = grades.prof1_grade_crit_3 || '';
                document.getElementById('epivlepon-crit4').value = grades.prof1_grade_crit_4 || '';
                document.getElementById('epivlepon-total').innerText = grades.prof1_final_grade || 'N/A';

                document.getElementById('member2-crit1').value = grades.prof2_grade_crit_1 || '';
                document.getElementById('member2-crit2').value = grades.prof2_grade_crit_2 || '';
                document.getElementById('member2-crit3').value = grades.prof2_grade_crit_3 || '';
                document.getElementById('member2-crit4').value = grades.prof2_grade_crit_4 || '';
                document.getElementById('member2-total').innerText = grades.prof2_final_grade || 'N/A';

                document.getElementById('member3-crit1').value = grades.prof3_grade_crit_1 || '';
                document.getElementById('member3-crit2').value = grades.prof3_grade_crit_2 || '';
                document.getElementById('member3-crit3').value = grades.prof3_grade_crit_3 || '';
                document.getElementById('member3-crit4').value = grades.prof3_grade_crit_4 || '';
                document.getElementById('member3-total').innerText = grades.prof3_final_grade || 'N/A';


                if (allGradesFilled) { 
                    saveGradesBtn.disabled = true;
                    document.getElementById('thesis-grading-status').innerText = grades.date_requested || 'N/A';
                    const stepsVathmologisi_ = document.querySelectorAll('.progress-timeline .step');
                    resetProgressModal();
                    updateSteps(stepsVathmologisi_, 5, 5);
                } else {
                    if (loggedInEmail === grades.prof1) {
                        if(grades.prof1_grade_crit_1 && grades.prof1_grade_crit_2 && grades.prof1_grade_crit_3 && grades.prof1_grade_crit_4){
                            saveGradesBtn.disabled = true;
                            document.getElementById('epivlepon-crit1').disabled = true;
                            document.getElementById('epivlepon-crit2').disabled = true;
                            document.getElementById('epivlepon-crit3').disabled = true;
                            document.getElementById('epivlepon-crit4').disabled = true;
                        }else{
                            document.getElementById('epivlepon-crit1').disabled = false;
                            document.getElementById('epivlepon-crit2').disabled = false;
                            document.getElementById('epivlepon-crit3').disabled = false;
                            document.getElementById('epivlepon-crit4').disabled = false;
                            setSaveButton(saveGradesBtn, thesisId, 'epivlepon');
                        }                    
                    } else if (loggedInEmail === grades.prof2) {
                        if(grades.prof2_grade_crit_1 && grades.prof2_grade_crit_2 && grades.prof2_grade_crit_3 && grades.prof2_grade_crit_4){
                            saveGradesBtn.disabled = true;
                            document.getElementById('member2-crit1').disabled = true;
                            document.getElementById('member2-crit2').disabled = true;
                            document.getElementById('member2-crit3').disabled = true;
                            document.getElementById('member2-crit4').disabled = true;
                        }else{
                            document.getElementById('member2-crit1').disabled = false;
                            document.getElementById('member2-crit2').disabled = false;
                            document.getElementById('member2-crit3').disabled = false;
                            document.getElementById('member2-crit4').disabled = false;
                            setSaveButton(saveGradesBtn, thesisId, 'member2');
                        }
                    } else if (loggedInEmail === grades.prof3) {
                        if(grades.prof3_grade_crit_1 && grades.prof3_grade_crit_2 && grades.prof3_grade_crit_3 && grades.prof3_grade_crit_4){
                            saveGradesBtn.disabled = true;
                            document.getElementById('member3-crit1').disabled = true;
                            document.getElementById('member3-crit2').disabled = true;
                            document.getElementById('member3-crit3').disabled = true;
                            document.getElementById('member3-crit4').disabled = true;
                        }else{
                            document.getElementById('member3-crit1').disabled = false;
                            document.getElementById('member3-crit2').disabled = false;
                            document.getElementById('member3-crit3').disabled = false;
                            document.getElementById('member3-crit4').disabled = false;
                            setSaveButton(saveGradesBtn, thesisId, 'member3');
                        }
                    } else {
                        console.error('den vrethike pote email kathigiti idio me ths vashs. email:', loggedInEmail);
                    }
                }
            } else {
                showNotification('Δεν βρέθηκαν δεδομένα βαθμολογίας.', 'error');
            }
        })
        .catch((error) => {
            console.error('Σφάλμα κατά την ανάκτηση βαθμολογιών:', error);
            showNotification('Σφάλμα κατά την ανάκτηση βαθμολογιών.', 'error');
        });
}
//kalei to koumpi me swsta attributes
function setSaveButton(button, thesisId, professorType) {
    button.style.display = 'inline-block';
    button.onclick = () => {
        showDenyConfirmationModal(
            'Οριστικοποίηση βαθμολογίας',
            'Είστε σίγουροι ότι έχετε εισάγει τη σωστή βαθμολογία;',
            'ΔΕΝ θα μπορείτε να την επεξεργαστείτε μετά την καταχώρηση, παρά μόνο εάν επικοινωνήσετε με το τεχνικό τμήμα.',
            () => saveGrades(thesisId, professorType)
        );
    };
}
// pairnei ta data vathmologias apo ta swsta input kai ta stelnei sto api
function saveGrades(thesisId, professorType) {
    const crit1 = document.getElementById(`${professorType}-crit1`).value;
    const crit2 = document.getElementById(`${professorType}-crit2`).value;
    const crit3 = document.getElementById(`${professorType}-crit3`).value;
    const crit4 = document.getElementById(`${professorType}-crit4`).value;

    if (!crit1 || !crit2 || !crit3 || !crit4) {
        showNotification('Παρακαλώ συμπληρώστε όλα τα πεδία βαθμολογίας.', 'error');
        return;
    }

    const gradeData = {
        thesis_id: thesisId,
        professor_type: professorType,
        grades: {
            crit1: parseFloat(crit1),
            crit2: parseFloat(crit2),
            crit3: parseFloat(crit3),
            crit4: parseFloat(crit4),
        },
    };

    fetch('../api/save_grades.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(gradeData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('thesisModal'));
                if (modal) {
                    modal.hide();
                }


                showNotification('Η βαθμολογία αποθηκεύτηκε επιτυχώς.', 'success');
                fetchAndDisplayGrades(thesisId);
            } else {
                showNotification(`Αποτυχία αποθήκευσης: ${data.message}`, 'error');
            }
        })
        .catch((error) => {
            console.error('Σφάλμα αποστολής:', error);
            showNotification('Παρουσιάστηκε σφάλμα κατά την αποθήκευση της βαθμολογίας.', 'error');
        });
}
//range 0-10 me vhma 0.5 valudated onblur (meaning otan stamathsei o xrhsths na grafei). kaleitai kathe fora pou allazei to value apo html
function validateRange(input) {
    let value = parseFloat(input.value);

    if (value < 0) {
        value = 0.00;
    }
    else if (value > 10) {
        value = undefined;
    }

    value = Math.round(value * 2) / 2;

    input.value = value.toFixed(2);
}
//vazei koumpi sto status bar
function addClickableStep(stepId, onClick) {
    const oldStepElement = document.getElementById(stepId);
    if (!oldStepElement) return;

    const newStepElement = oldStepElement.cloneNode(true);

    oldStepElement.replaceWith(newStepElement);

    newStepElement.classList.add('clickable');
    newStepElement.addEventListener('click', onClick);
}
//modal epivevaiwshs aporripshs foithth, idio kai sth grammateia
function showDenyConfirmationModal(title, message1, message2 = '', onConfirm) { //typeof gia to message 2, gia na mhn xreiazetai h klhsh na exei 2o argument message
    const modalHtml = `
        <div class="modal fade" id="denyConfirmationModal" tabindex="-1" aria-labelledby="denyConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content shadow-lg" style="border-radius: 0.5rem; z-index: 1056;">
                    <div class="modal-header bg-danger text-white">
                        <h6 class="modal-title fw-bold" id="denyConfirmationModalLabel">${title}</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3"></i>
                        <p class="fs-6">${message1}</p>
                        ${typeof message2 === 'string' && message2 ? `<p class="fs-6"><small>${message2}</small></p>` : ''}
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Άκυρο
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="confirmDenyButton">
                            <i class="bi bi-check-circle"></i> Επιβεβαίωση
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);

    const confirmationModal = new bootstrap.Modal(document.getElementById('denyConfirmationModal'));
    confirmationModal.show();

    document.getElementById('confirmDenyButton').addEventListener('click', () => {
        confirmationModal.hide();
        modalContainer.remove();
        if (typeof onConfirm === 'function') onConfirm();
    });

    document.getElementById('denyConfirmationModal').addEventListener('hidden.bs.modal', () => {
        modalContainer.remove();
    });
}
//allazei to status kai xrhsimopoiei to showDenyConfirmationModal gia diathesimi
function updateThesisStatus(thesisId, newStatus) {
    const apiUrl =
        newStatus === "diathesimi" ? "../api/deny_student.php" : "../api/update_thesis_status.php";

    const executeStatusChange = () => {
        fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                thesisId: thesisId,
                newStatus: newStatus
            }),
        })
        .then(response => response.json())
        .then(data => {
            //console.log('updateThesisStatus response:', data); //debuggggggggggggggggggggggggggggggggggggggggggggg

            if (data.success) {
                const message = (newStatus === "diathesimi")
                    ? "Η διπλωματική έγινε διαθέσιμη."
                    : `Η κατάσταση της διπλωματικής άλλαξε επιτυχώς σε "${getStatusText(newStatus)}".`;

                showNotification(message, "success");

                const modalElement = document.getElementById("thesisModal");
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                loadMyTheses();
            } else {
                showNotification(data.message || "Αποτυχία αλλαγής κατάστασης.", "error");
            }
        })
        .catch(error => {
            console.error("Σφάλμα κατά την αλλαγή κατάστασης:", error);
            showNotification("Σφάλμα κατά την αλλαγή κατάστασης.", "error");
        });
    };

    if (newStatus === "diathesimi") {
        showDenyConfirmationModal(
            "Επιβεβαίωση Απόρριψης Φοιτητή",
            "Είστε βέβαιοι ότι θέλετε να απορρίψετε τον φοιτητή και να επαναφέρετε τη διπλωματική σε διαθέσιμη κατάσταση;",
            '',
            executeStatusChange
        );
    } else {
        executeStatusChange();
    }
}
//anakoinwsh anakoinwshs lol
function setupAnnouncementForm(thesisId) {
    const announcementForm = document.getElementById('exam-announcement-form');
    const submitButton = document.getElementById('announce-exam-submit-btn');
    const announcementField = document.getElementById('announcement');

    if (announcementForm) {
        announcementField.addEventListener('input', () => {
            if (announcementField.value.trim() !== "") {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        });

        announcementForm.addEventListener('submit', function(event) {
            event.preventDefault();
            submitButton.disabled = true;

            const payload = {
                id_diplomatikis: thesisId,
                ann_body: announcementField.value.trim(),
                status: 'public'
            };

            fetch('../api/announce_exam.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                .then(response => response.json())
                .then(data => {
                    submitButton.disabled = false;

                    if (data.success) {
                        showNotification('Η ανακοίνωση εξέτασης δημοσιεύθηκε επιτυχώς.', 'success');
                        loadMyTheses();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(() => {
                    submitButton.disabled = false;
                    showNotification('Σφάλμα κατά την αποστολή της ανακοίνωσης.', 'error');
                });
        });
    }
}

// *** REQUEST HANDLING *** \\
// load ta requests tou kathigiti
function loadProfessorRequests(currentPage = 1) {
    // Fetch and display professor requests me pagination
    const requestsPerPage = 7;

    fetch('../api/get_my_requests.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#invitations-table-body');
            const paginationContainer = document.querySelector('#pagination-inv-container');
            tableBody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (data.success && data.requests.length > 0) {
                const totalRequests = data.requests.length;
                const totalPages = Math.ceil(totalRequests / requestsPerPage);
                const startIndex = (currentPage - 1) * requestsPerPage;
                const requestsToShow = data.requests.slice(startIndex, startIndex + requestsPerPage);

                requestsToShow.forEach(request => {
                    const isHandled = request.status !== 'pending';
                    const statusBadge = getRequestStatusBadge(request.status);

                    const row = `
                        <tr>
                            <td>${request.student_name}</td>
                            <td>${request.professor_name}</td>
                            <td>${request.thesis_topic}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-success btn-sm accept-btn" data-id="${request.request_id}" ${isHandled ? 'disabled' : ''}>
                                    Αποδοχή
                                </button>
                                <button class="btn btn-danger btn-sm reject-btn" data-id="${request.request_id}" ${isHandled ? 'disabled' : ''}>
                                    Απόρριψη
                                </button>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });

                attachRequestActions();

                // selidopoihsh
                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        const pageItem = `
                            <li class="page-item ${activeClass}">
                                <button class="page-link" onclick="loadProfessorRequests(${i})">${i}</button>
                            </li>`;
                        paginationContainer.insertAdjacentHTML('beforeend', pageItem);
                    }
                }
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Δεν υπάρχουν προσκλήσεις.</td>
                    </tr>`;
            }
        })
        .catch(() => showNotification('Σφάλμα κατά τη φόρτωση των προσκλήσεων.', 'error'));
}
// attach functionality koumpia
function attachRequestActions() {
    document.querySelectorAll('.accept-btn').forEach(button => {
        button.addEventListener('click', () => handleRequestAction(button.dataset.id, 'accepted'));
    });

    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', () => handleRequestAction(button.dataset.id, 'rejected'));
    });
}
// accept/reject actions
function handleRequestAction(requestId, action) {
    fetch('../api/update_request_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                requestId,
                action
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Η ενέργεια ολοκληρώθηκε επιτυχώς.', 'info');
                loadProfessorRequests();
            } else {
                showNotification(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating request status:', error);
            showNotification('Σφάλμα κατά την ενημέρωση της κατάστασης της πρόσκλησης.', 'error');
        });
}

// *** STUDENT SEARCH *** \\
function setupStudentSearch(searchInputId, suggestionsListId, studentAmInputId) {
    const studentSearchInput = document.getElementById(searchInputId);
    const suggestionsList = document.getElementById(suggestionsListId);
    const studentAmInput = document.getElementById(studentAmInputId);

    if (!studentSearchInput || !suggestionsList) return;

    studentSearchInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (query === '') {
            suggestionsList.innerHTML = '';
            suggestionsList.style.display = 'none';
            return;
        }

        if (query.length > 0) {
            fetch(`../api/student_search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsList.innerHTML = '';
                    if (data.length) {
                        suggestionsList.style.display = 'block';
                        data.forEach(student => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action';
                            li.textContent = `${student.name} (${student.am})`;
                            li.onclick = () => {
                                studentSearchInput.value = `${student.name} (${student.am})`;
                                studentAmInput.value = student.am;
                                suggestionsList.style.display = 'none';
                            };
                            suggestionsList.appendChild(li);
                        });
                    } else {
                        suggestionsList.style.display = 'none';
                    }
                })
                .catch(() => showNotification('Σφάλμα κατά την αναζήτηση φοιτητών.', 'error'));
        }
    });

    document.addEventListener('click', function(e) {
        if (!studentSearchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });
}

// *** FORM HANDLING *** \\
function setupPublishForm() {
    const publishForm = document.getElementById('publish-form');
    if (publishForm) {
        publishForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            fetch('../api/publish_thesis.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        this.reset();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(() => showNotification('Σφάλμα κατά την υποβολή της φόρμας.', 'error'));
        });
    }
}

// *** EXPORT FUNCTIONS *** \\
// Handle main export button click (default CSV export)
function handleExport() {
    tbtojson();
}
// Handle dropdown menu option selection for export format
function handleExportFormatChange(format) {
    if (format === 'CSV') {
        tbtocsv();
    } else if (format === 'JSON') {
        tbtojson();
    } else {
        showNotification('Παρακαλώ επιλέξτε έγκυρη μορφή εξαγωγής.', 'info');
    }
}
// Export data to JSON format
function tbtojson() {
    fetch('../api/get_my_theses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const jsonData = JSON.stringify(data.theses, null, 2);
                downloadFile(jsonData, 'theses.json', 'application/json');
            } else {
                showNotification('Σφάλμα κατά την εξαγωγή σε JSON.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching theses:', error);
            showNotification('Σφάλμα κατά την εξαγωγή σε JSON.', 'error');
        });
}
// Export data to CSV format
function tbtocsv() {
    fetch('../api/get_my_theses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let csv = 'ID;Topic;Student;Start Date;Exam Date;Status\n'; //to excel mou mesa mporei na xreiazetai to apo katw
                //let csv = 'ID,Topic,Student,Start Date,Exam Date,Status\n';
                data.theses.forEach(thesis => {
                    csv += `${thesis.id};"${thesis.topic}";"${thesis.student || ''}";"${thesis.start_date || ''}";"${thesis.exam_date || ''}";"${thesis.status}"\n`;
                });
                downloadFile(csv, 'theses.csv', 'text/csv');
            } else {
                showNotification('Σφάλμα κατά την εξαγωγή σε CSV.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching theses:', error);
            showNotification('Σφάλμα κατά την εξαγωγή σε CSV.', 'error');
        });
}
// Download the generated file
function downloadFile(content, fileName, mimeType) {
    const blob = new Blob([content], {
        type: mimeType
    });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// *** MAPPING *** \\
function getStatusText(status) {
    const statusMapping = {
        pending: 'Προς Ανάθεση',
        pros_anathesi: 'Προς Ανάθεση',
        active: 'Ενεργή',
        energi: 'Ενεργή',
        completed: 'Ολοκληρωμένη',
        oloklirwmeni: 'Ολοκληρωμένη',
        cancelled: 'Ακυρωμένη',
        akurwmeni: 'Ακυρωμένη',
        exetasi: 'Προς Εξέταση',
        diathesimi: 'Διαθέσιμη',
        pros_egrisi: 'Προς έγκριση',
        vathmologisi: 'Βαθμολόγηση'
    };
    return statusMapping[status] || 'Άγνωστη Κατάσταση';
}
function getRoleText(status) {
    const statusRoleMapping = {
        Supervisor: 'Επιβλέπων',
        Member: 'Μέλος τριμελούς'
    };
    return statusRoleMapping[status] || 'Άγνωστη';
}
function getRequestStatusBadge(status) {
    let badgeClass = '';
    let badgeText = '';

    switch (status) {
        case 'accepted':
            badgeClass = 'bg-success';
            badgeText = 'Αποδεκτή';
            break;
        case 'rejected':
            badgeClass = 'bg-danger';
            badgeText = 'Απορρίφθηκε';
            break;
        case 'pending':
            badgeClass = 'bg-warning text-dark';
            badgeText = 'Εκκρεμεί';
            break;
        case 'canceled':
            badgeClass = 'bg-secondary';
            badgeText = 'Ακυρώθηκε αυτόματα';
            break;
        case 'vathmologisi':
            badgeClass = 'bg-success';
            badgeText = 'Προς βαθμολόγηση';
            break;
        default:
            badgeClass = 'bg-secondary';
            badgeText = 'Άγνωστη';
    }
    return `<span class="badge ${badgeClass}">${badgeText}</span>`;
}


// *** STATISTICS *** \\
let charts = {};

function loadStatistics() {
    fetch('../api/get_statistics.php')
        .then((response) => {
            if (!response.ok) {
                throw new Error('Αποτυχία σύνδεσης με τον server.');
            }
            return response.json();
        })
        .then((data) => {
            if (!data.success) {
                throw new Error(data.message || 'Αποτυχία φόρτωσης στατιστικών.');
            }

            const labels = ['Ως Επιβλέπων', 'Ως Μέλος Επιτροπής'];

            createPieChart(
                'thesesPieChart',
                labels,
                [data.supervisor.total_theses || 0, data.committee_member.total_theses || 0]
            );

            createChart(
                'avgGradeChart',
                labels,
                [data.supervisor.avg_grade || 0, data.committee_member.avg_grade || 0],
                'Μέσος Βαθμός'
            );

            createChart(
                'avgCompletionTimeChart',
                labels,
                [data.supervisor.avg_completion_time || 0, data.committee_member.avg_completion_time || 0],
                'Μέσος Χρόνος Ολοκλήρωσης (Ημέρες)'
            );
        })
        .catch((error) => {
            console.error('Σφάλμα στη φόρτωση στατιστικών:', error);
            showNotification('Αποτυχία φόρτωσης στατιστικών.', 'error');
        });
}

//kshlwnei to prohgoumeno graph an uparxei
function destroyExistingChart(canvasId) {
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }
}

function createChart(canvasId, labels, data, title) {
    destroyExistingChart(canvasId); //ksilwma to prohgoumeno graph an uparxei

    const ctx = document.getElementById(canvasId).getContext('2d');
    charts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: ['#4caf50', '#2196f3'],
                borderColor: ['#388e3c', '#1976d2'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            }
        }
    });
}

function createPieChart(canvasId, labels, data) {
    destroyExistingChart(canvasId); //ksilwma to prohgoumeno graph an uparxei

    const ctx = document.getElementById(canvasId).getContext('2d');
    charts[canvasId] = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#4caf50', '#2196f3']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let total = data.reduce((sum, val) => sum + val, 0);
                            let percentage = ((context.raw / total) * 100).toFixed(2);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}