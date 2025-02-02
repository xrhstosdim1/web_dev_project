
// *** ΔΙΑΘΕΣΙΜΕΣ ΔΙΠΛΩΜΑΤΙΚΕΣ *** \\
// fetch tis diathesimes diplomatikes kai pagination
function fetchAvailableTheses(currentPage = 1) {
	const thesesPerPage = 10;

	fetch('../api/get_available_theses.php')
		.then(response => response.json())
		.then(data => {
			const tableBody = document.querySelector('#theses-table tbody');
			const paginationContainer = document.getElementById('pagination-container');
			tableBody.innerHTML = '';
			paginationContainer.innerHTML = '';

			if (data.success && data.theses.length > 0) {
				const totalTheses = data.theses.length;
				const totalPages = Math.ceil(totalTheses / thesesPerPage);
				const startIndex = (currentPage - 1) * thesesPerPage;
				const endIndex = startIndex + thesesPerPage;
				const thesesToShow = data.theses.slice(startIndex, endIndex);

				thesesToShow.forEach(thesis => {
					const truncatedTopic = thesis.topic.length > 55 ?
						thesis.topic.substring(0, 55) + ' ...' :
						thesis.topic;
					const truncatedSummary = thesis.summary.length > 50 ?
						thesis.summary.substring(0, 50) + ' ...' :
						thesis.summary;

					const row = `
                            <tr>
                                <td>${thesis.id}</td>
                                <td>${thesis.professor_name} ${thesis.professor_surname}</td>
                                <td>${truncatedTopic}</td>
                                <td>${truncatedSummary}</td>
                                <td>
                                    <button class="view-button btn btn-primary btn-sm" data-id="${thesis.id}">Προβολή</button>
                                </td>
                            </tr>
                        `;
					tableBody.insertAdjacentHTML('beforeend', row);
				});

				attachViewButtons();

				for (let i = 1; i <= totalPages; i++) {
					const activeClass = i === currentPage ? 'active' : '';
					const pageItem = `
                            <li class="page-item ${activeClass}">
                                <button class="page-link" onclick="fetchAvailableTheses(${i})">${i}</button>
                            </li>
                        `;
					paginationContainer.insertAdjacentHTML('beforeend', pageItem);
				}
			} else {
				tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Δεν υπάρχουν διαθέσιμα θέματα.</td></tr>';
			}
		})
		.catch(error => {
			console.error('Error fetching theses:', error);
			showNotification('Σφάλμα κατά τη φόρτωση των διαθέσιμων θεμάτων.', 'error');
		});
}
// view button
function attachViewButtons() {
	const viewButtons = document.querySelectorAll('.view-button');
	viewButtons.forEach(button => {
		button.addEventListener('click', () => {
			const thesisId = button.getAttribute('data-id');
			openThesisModal(thesisId);
		});
	});
}
// open modal
function openThesisModal(thesisId) {
	fetch(`../api/get_thesis_details.php?id=${thesisId}`)
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				document.getElementById('thesis-title').textContent = data.details.topic || 'N/A';
				document.getElementById('thesis-description').textContent = data.details.summary || 'N/A';

				const fileName = data.details.file_name || 'N/A';
				const fileLink = document.getElementById('thesis-file-link');
				if (fileName !== 'N/A') {
					fileLink.textContent = fileName;
					fileLink.href = `../uploads/${fileName}`;
					fileLink.classList.remove('text-muted');
					fileLink.classList.add('text-primary');
				} else {
					fileLink.textContent = 'Δεν υπάρχει διαθέσιμο αρχείο';
					fileLink.href = '#';
					fileLink.classList.remove('text-primary');
					fileLink.classList.add('text-muted');
				}

				const selectButton = document.getElementById('select-thesis-button');
				selectButton.setAttribute('data-id', thesisId);

				const modal = new bootstrap.Modal(document.getElementById('thesisModal'));
				modal.show();
			} else {
				showNotification('Δεν βρέθηκαν λεπτομέρειες για αυτή τη διπλωματική.', 'error');
			}
		})
		.catch(error => {
			console.error('Error fetching thesis details:', error);
			showNotification('Σφάλμα κατά τη φόρτωση των λεπτομερειών.', 'error');
		});
}
// select thesis
document.getElementById('select-thesis-button').addEventListener('click', function() {
	const thesisId = this.getAttribute('data-id');

	if (!thesisId) {
		console.error('Το thesisId δεν βρέθηκε στο κουμπί "Επιλογή Θέματος".');
		showNotification('Σφάλμα: Δεν βρέθηκε το ID της διπλωματικής.', 'error');
		return;
	}

	fetch('../api/student_select_thesis.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				thesisID: thesisId
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				showNotification('Επιτυχής επιλογή θέματος!', 'success');
				const modal = bootstrap.Modal.getInstance(document.getElementById('thesisModal'));
				modal.hide();
				fetchAvailableTheses();
			} else {
				showNotification(data.message || 'Αποτυχία επιλογής θέματος.', 'error');
			}
		})
		.catch(error => {
			console.error('Error selecting thesis:', error);
			showNotification('Σφάλμα κατά την επιλογή θέματος.', 'error');
		});
});



// *** ΠΡΟΣΚΛΗΣΗ ΚΑΘΗΓΗΤΩΝ *** \\

// search prof
function setupProfessorSearch() {
	const professorSearchInput = document.getElementById('professor-search');
	const suggestionsList = document.getElementById('professors-suggestions');
	const sendInviteButton = document.getElementById('send-invite-button');

	professorSearchInput.addEventListener('input', function() {
		const query = this.value.trim();

		if (query === '') {
			suggestionsList.innerHTML = '';
			suggestionsList.style.display = 'none';
			sendInviteButton.disabled = true;
			return;
		}

		if (query.length > 0) {
			fetch(`../api/professor_search.php?q=${encodeURIComponent(query)}`)
				.then(response => response.json())
				.then(data => {
					suggestionsList.innerHTML = '';

					if (data.length) {
						suggestionsList.style.display = 'block';
						data.forEach(professor => {
							const li = document.createElement('li');
							li.className = 'list-group-item list-group-item-action';
							li.textContent = `${professor.name} (${professor.email})`;
							li.onclick = () => {
								professorSearchInput.value = professor.email;
								suggestionsList.style.display = 'none';
								sendInviteButton.disabled = false;
							};
							suggestionsList.appendChild(li);
						});
					} else {
						suggestionsList.style.display = 'none';
					}
				})
				.catch(() => showNotification('Σφάλμα κατά την αναζήτηση καθηγητών', 'error'));
		}
	});
}
// submit request
function setupInviteForm() {
	const sendInviteButton = document.getElementById('send-invite-button');
	const professorSearchInput = document.getElementById('professor-search');

	sendInviteButton.addEventListener('click', () => {
		const professorEmail = professorSearchInput.value.trim();

		if (!professorEmail) {
			showNotification('Παρακαλώ εισάγετε email καθηγητή.', 'error');
			return;
		}

		fetch('../api/get_student_thesis.php')
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data.length > 0) {
					const thesisID = data.data[0].id;

					return fetch('../api/send_professor_request.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({
							thesisID: thesisID,
							professorEmail: professorEmail
						})
					});
				} else {
					throw new Error('Δεν βρέθηκε διπλωματική εργασία.');
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					showNotification(data.message, 'success');
					fetchAndDisplayRequests();
				} else {
					showNotification(data.message, 'error');
				}
			})
			.catch(error => {
				console.error('Σφάλμα:', error);
				showNotification('Σφάλμα κατά την αποστολή της πρόσκλησης.', 'error');
			});
	});
}



// *** ΠΛΗΡΟΦΟΡΙΕΣ ΧΡΗΣΤΗ *** \\

// load stoixeia xrhsth
function fetchUserProfile() {
	fetch('../api/get_profile.php')
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				const user = data.user;

				document.getElementById('profile-first-name').value = user.first_name;
				document.getElementById('profile-last-name').value = user.last_name;
				document.getElementById('profile-email').value = user.email;
				document.getElementById('profile-am').value = user.am;
				document.getElementById('profile-street').value = user.street;
				document.getElementById('profile-str-number').value = user.str_number;
				document.getElementById('profile-city').value = user.city;
				document.getElementById('profile-postcode').value = user.postcode;
				document.getElementById('profile-mobile').value = user.mobile_phone || '';
				document.getElementById('profile-landline').value = user.landline_phone || '';
			} else {
				showNotification('Σφάλμα κατά τη φόρτωση των στοιχείων του προφίλ.', 'error');
			}
		})
		.catch(error => {
			console.error('Error fetching user profile:', error);
			showNotification('Σφάλμα κατά τη φόρτωση των στοιχείων του προφίλ.', 'error');
		});
}
// update stoixeia xrhsth
function setupProfileForm() {
	const profileForm = document.getElementById('profileForm');
	if (profileForm) {
		profileForm.addEventListener('submit', (event) => {
			event.preventDefault();

			const formData = new FormData();
			let hasChanges = false;

			document.querySelectorAll('#profileForm input').forEach(input => {
				const originalValue = input.getAttribute('data-original') || '';
				const currentValue = input.value.trim();

				//null handling
				if (originalValue !== currentValue || currentValue === '') {
					formData.append(input.name, currentValue === '' ? 'NULL' : currentValue);
					hasChanges = true;
				}
			});

			if (!hasChanges) {
				showNotification('Δεν έγιναν αλλαγές για ενημέρωση.', 'info');
				return;
			}

			fetch('../api/update_profile.php', {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						showNotification(data.message, 'success');
					} else {
						showNotification(data.message, 'error');
					}
				})
				.catch(error => {
					console.error('Σφάλμα κατά την ενημέρωση των δεδομένων:', error);
					showNotification('Σφάλμα κατά την ενημέρωση των δεδομένων.', 'error');
				});
		});
	}
}





// *** ΔΙΠΛΩΜΑΤΙΚΗ ΧΡΗΣΤΗ *** \\

// fetch data for thesis
function fetchThesisData() {
	fetch('../api/get_student_thesis.php')
		.then(response => response.json())
		.then(data => {
			if (!data.success) {
				throw new Error(data.message || "API returned an error.");
			}

			const noThesisMessage = document.getElementById('no-thesis-message');
			const thesisInfo = document.getElementById('thesis-info');
			const progressBar = document.getElementById('status-progress');
			const timeSinceAssignment = document.getElementById('time-since-assignment');

			if (data.data.length > 0) {
				const thesis = data.data[0];

				const statusMapping = {
					"pros_anathesi": {
						label: "Ανάθεση",
						progress: 16
					},
					"pros_egrisi": {
						label: "Έγκριση από Γραμματεία",
						progress: 33
					},
					"energi": {
						label: "Ενεργή",
						progress: 50
					},
					"exetasi": {
						label: "Εξέταση",
						progress: 66
					},
					"vathmologisi": {
						label: "Εκρεμμής βαθμολογία",
						progress: 83
					},
					"oloklirwmeni": {
						label: "Ολοκληρωμένη",
						progress: 100
					}
				};

				const statusDetails = statusMapping[thesis.status] || {
					label: "Άγνωστη κατάσταση",
					progress: 0
				};
				progressBar.style.width = `${statusDetails.progress}%`;
				progressBar.textContent = statusDetails.label;

				document.getElementById('thesis-topic').textContent = thesis.topic || 'Δεν υπάρχει θέμα';
				document.getElementById('thesis-summary').textContent = thesis.summary || 'Δεν υπάρχει περιγραφή';


				const fileLink = document.getElementById('my-thesis-file-link');
				fileLink.innerHTML = thesis.proff_file ?
					`<a href="../uploads/${thesis.proff_file}" target="_blank" class="text-decoration-none text-primary">${thesis.proff_file}</a>` :
					'Δεν έχει αναρτηθεί αρχείο από τον καθηγητή.';

				document.getElementById('supervisor-member').textContent = thesis.supervisor || 'Δεν έχει οριστεί';
				document.getElementById('committee-member1').textContent = thesis.member1 || 'Δεν έχει οριστεί';
				document.getElementById('committee-member2').textContent = thesis.member2 || 'Δεν έχει οριστεί';

				if(thesis.status != 'oloklirwmeni') {
					if (thesis.start_date) {
						const startDate = new Date(thesis.start_date.replace(' ', 'T'));
						startAutoUpdatingCounter(startDate);
					} else {
						timeSinceAssignment.textContent = 'Η ανάθεση δεν έχει εγκριθεί.';
					}
				} else {
					timeSinceAssignment.textContent = '-';
				}
				thesisInfo.style.display = 'block';
				noThesisMessage.style.display = 'none';
			} else {
				thesisInfo.style.display = 'none';
				noThesisMessage.style.display = 'block';
			}
		})
		.catch(error => {
			console.error('Error fetching thesis data:', error);
			showNotification('Δεν έχει επιλεχθεί θέμα διπλωματικής.', 'error');
		});
}
// progress bar 
function updateProgressBar(status) {
	const statusMapping = {
		"pros_anathesi": "Προς Ανάθεση",
		"pros_egkrisi": "Προς Έγκριση",
		"energi": "Ενεργή",
		"exetasi": "Προς Εξέταση",
		"oloklirwmeni": "Ολοκληρωμένη"
	};

	const progressMapping = {
		"pros_anathesi": 0,
		"pros_egkrisi": 25,
		"energi": 50,
		"exetasi": 75,
		"oloklirwmeni": 100
	};

	const currentStatus = statusMapping[status] || "Άγνωστη κατάσταση";
	const percentage = progressMapping[status] || 0;

	const statusProgress = document.getElementById("status-progress");
	statusProgress.style.width = `${percentage}%`;
	statusProgress.setAttribute("aria-valuenow", percentage);
	statusProgress.textContent = currentStatus;

	statusProgress.className = `progress-bar stage-${Math.ceil(percentage / 25)}`;
}
// timer function //TODO:: na paei grammateia
function startAutoUpdatingCounter(startDate) {
	function updateCounter() {
		const now = new Date();
		const timeDiff = now - startDate;

		if (timeDiff > 0) {
			const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
			const hours = Math.floor((timeDiff / (1000 * 60 * 60)) % 24);
			const minutes = Math.floor((timeDiff / (1000 * 60)) % 60);
			const seconds = Math.floor((timeDiff / 1000) % 60);

			document.getElementById('time-since-assignment').textContent =
				`${days} μέρες, ${hours} ώρες, ${minutes} λεπτά, ${seconds} δευτερόλεπτα`;
		} else {
			document.getElementById('time-since-assignment').textContent = 'Η ανάθεση δεν έχει εγκριθεί επίσημα';
		}
	}

	updateCounter();
	setInterval(updateCounter, 1000);
}
// function to fetch requests
function fetchAndDisplayRequests() {
	const tableBody = document.getElementById('requests-table-body');

	fetch('../api/get_student_thesis.php')
		.then(response => response.json())
		.then(data => {
			if (data.success && data.data.length > 0) {
				const thesisID = data.data[0].id;

				return fetch(`../api/get_requests_for_a_thesis.php?id_diplomatikis=${thesisID}`);
			} else {
				throw new Error('Δεν βρέθηκε διπλωματική εργασία.');
			}
		})
		.then(response => response.json())
		.then(data => {
			if (data.requests.length === 0) {
				tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">Δεν υπάρχουν προσκλήσεις</td>
                        </tr>
                    `;
			} else {
				tableBody.innerHTML = data.requests.map(request => `
                        <tr>
                            <td>${request.professor_name}</td>
                            <td>${new Date(request.date_requested).toLocaleString()}</td>
                            <td>${getStatusBadge(request.status)}</td>
                            <td>${request.date_answered ? new Date(request.date_answered).toLocaleString() : 'N/A'}</td>
                        </tr>
                    `).join('');
			}
		})
		.catch(error => {
			tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">${error.message}</td>
                    </tr>
                `;
		});
}



// *** MAPS *** \\
//map gia status
const statusMapping = {
	"pros_anathesi": "Προς Ανάθεση",
	"pros_egkrisi": "Προς Έγκριση",
	"energi": "Ενεργή",
	"exetasi": "Προς Εξέταση",
	"oloklirwmeni": "Ολοκληρωμένη"
};
// map gia progress
const progressMapping = {
	"pros_anathesi": 0,
	"pros_egkrisi": 25,
	"energi": 50,
	"exetasi": 75,
	"oloklirwmeni": 100
};
// badge status view
function getStatusBadge(status) {
	switch (status) {
		case 'pending':
			return '<span class="badge bg-warning">Εκκρεμεί</span>';
		case 'accepted':
			return '<span class="badge bg-success">Αποδεκτή</span>';
		case 'rejected':
			return '<span class="badge bg-danger">Απορρίφθηκε</span>';
		case 'canceled':
			return '<span class="badge bg-secondary">Ακυρώθηκε αυτόματα</span>';
		default:
			return '<span class="badge bg-secondary">Άγνωστο</span>';
	}
}



// *** DOM LOADS *** \\
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.nav-link').forEach(link => {
		link.addEventListener('click', (e) => {
			e.preventDefault();

			const targetSection = link.getAttribute('data-section');

			document.querySelectorAll('.section').forEach(section => {
				section.classList.remove('active');
				section.style.display = 'none';
			});

			const sectionElement = document.getElementById(targetSection);
			if (sectionElement) {
				sectionElement.classList.add('active');
				sectionElement.style.display = 'block';

				switch (targetSection) {
					case 'my-thesis':
						fetchAndDisplayThesisStatus();
						break;
					case 'available':
						fetchAvailableTheses();
						break;
					case 'thesis-info':
						fetchThesisData();
						break;
					case 'profile':
						fetchUserProfile();
						setupProfileForm();
						break;
					default:
						console.warn(`No function associated with section: ${targetSection}`);
						break;
				}
			}
		});
	});

	const defaultSection = document.querySelector('.nav-link[data-section="my-thesis"]');
	if (defaultSection) {
		defaultSection.click();
	}
});

function fetchAndDisplayThesisStatus() {
	
	const contentContainer = document.getElementById('my-thesis-content');

	fetch('../api/get_student_thesis.php')
		.then(response => response.json())
		.then(data => {
			if (data.success && data.data.length > 0) {
				const thesis = data.data[0];
				const status = thesis.status;
				const id_dipl = thesis.id;

				switch (status) {
					case 'pros_anathesi':
						contentContainer.innerHTML = `
                                <div class="card shadow-sm border-0 p-4">
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-users fa-4x text-primary"></i>
                                            <h4 class="card-title text-primary fw-bold mt-3">Προς Ανάθεση</h4>
                                            <p class="card-text text-muted">
                                                Επιλέξτε καθηγητές για να προσκληθούν στην τριμελή επιτροπή της διπλωματικής σας.
                                            </p>
                                        </div>
                                        
                                        <div class="row g-4">
                                            <div class="col-12 mb-4">
                                                <div class="card  shadow-sm">
                                                    <div class="card-body">
                                                        <h5 class="fw-bold mb-3">Αναζήτηση Καθηγητή</h5>
                                                        <div class="input-group">
                                                            <input type="text" id="professor-search" class="form-control" placeholder="Πληκτρολογήστε όνομα ή email καθηγητή" autocomplete="off">
                                                            <button id="send-invite-button" class="btn btn-primary" type="button" disabled>
                                                                <i class="fas fa-paper-plane"></i> Αποστολή
                                                            </button>
                                                        </div>
                                                        <ul id="professors-suggestions" class="list-group mt-3 position-absolute" style="z-index: 1000; width: 100%; display: none;"></ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="card shadow-sm">
                                                    <div class="card-body">
                                                        <h5 class="fw-bold mb-3">Προσκλήσεις που έχουν σταλεί</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover align-middle text-center">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th class="text-nowrap">Καθηγητής</th>
                                                                        <th class="text-nowrap">Ημερομηνία Αποστολής</th>
                                                                        <th class="text-nowrap">Κατάσταση</th>
                                                                        <th class="text-nowrap">Ημερομηνία Απάντησης</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="requests-table-body">
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-muted">Δεν υπάρχουν προσκλήσεις</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
						setupProfessorSearch();
						setupInviteForm();
						fetchAndDisplayRequests();
						break;
					case 'pros_egrisi':
						contentContainer.innerHTML = `
                            <br>
                                <div class="card shadow-sm border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-hourglass-half fa-3x text-primary mb-3"></i>
                                        <h4 class="card-title fw-bold text-primary">Η αίτησή σας έχει αποσταλεί προς αξιολόγηση</h4>
                                        <p class="card-text">
                                            Η αίτησή σας για έναρξη εκπόνησης διπλωματικής εργασίας έχει σταλεί και 
                                            θα εξεταστεί από τη Γενική Συνέλευση του Τμήματος.
                                        </p>
                                    </div>
                                </div>
                            `;
						break;
					case 'energi':
						contentContainer.innerHTML = `
                                <div class="card shadow-sm border-0">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h4 class="card-title fw-bold text-success">Η διπλωματική σας είναι ενεργή!</h4>
                                        <p class="card-text">
                                            Όταν είστε έτοιμος επικοινωνήστε με τον επιβλέποντα καθηγητή σας για να ορίσετε τρόπο εξέτασης.
                                        </p>
                                    </div>
                                </div>
                            `;
						break;
					case 'exetasi':
						fetchExternalLinks(id_dipl);
						contentContainer.innerHTML = `
                                <div class="card shadow-sm border-0 p-4">
                                    <div class="card-body">
                                        <!-- Εικονίδιο πάνω από τον τίτλο -->
                                        <div class="text-center mb-3">
                                            <i class="fas fa-chalkboard-teacher fa-3x text-warning"></i>
                                        </div>
                                        <h4 class="card-title text-warning text-center fw-bold mb-4">Η διπλωματική σας βρίσκεται σε στάδιο εξέτασης</h4>
                                        <p class="card-text text-center text-muted mb-4">
                                            Παρακαλώ ολοκληρώστε τα ακόλουθα βήματα για να εξεταστείτε:
                                        </p>
                                        <br>
                                        <br>
                                        <br>
                                        <div class="steps-container">
                                            <div class="step mb-4">
                                                <h5 class="fw-bold mb-2"><span class="text-primary">1.</span> Ανάρτηση πρόχειρου κειμένου *</h5>
                                                <p class="text-muted">
                                                    Ανεβάστε το πρόχειρο κείμενο της διπλωματικής σας εργασίας που θα είναι ορατό από την τριμελή επιτροπή.
                                                </p>
                                                <div class="input-group">
                                                    <input type="file" id="draft-upload" class="form-control mb-2">
                                                    <button class="btn btn-primary btn-sm" onclick="uploadDraft(${id_dipl})">Ανάρτηση Αρχείου</button>
                                                </div>
                                            </div>
                            
                                            <div class="step mb-4">
                                                <h5 class="fw-bold mb-2"><span class="text-primary">2.</span> Ανάρτηση συνδέσμων:</h5>
                                                <p class="text-muted">
                                                    Προσθέστε συνδέσμους προς εξωτερικό υλικό (Google Drive, YouTube κλπ).
                                                </p>
                                                <div class="input-group">
                                                    <input type="text" id="external-link" class="form-control mb-2" placeholder="www.example.com">
                                                    <button class="btn btn-primary btn-sm" onclick="addExternalLink(${id_dipl})">Προσθήκη Συνδέσμου</button>
                                                </div>
                                                <div id="external-links-container" class="mt-2">
                                                    <!-- Δυναμική εμφάνιση συνδέσμων -->
                                                </div>
                                            </div>
                            
                                            <div class="step">
												<h5 class="fw-bold mb-2"><span class="text-primary">3.</span> Καταχώρηση στοιχείων εξέτασης *</h5>
												<p class="text-muted">
													Εισάγετε ημερομηνία, ώρα, και λεπτομέρειες εξέτασης (αίθουσα ή σύνδεσμο σύνδεσης).
												</p>
												<div class="row g-3">
													<div class="col-md-4">
														<input type="date" id="exam-date" class="form-control" placeholder="Ημερομηνία">
													</div>
													<div class="col-md-4">
														<input type="time" id="exam-time" class="form-control" placeholder="Ώρα">
													</div>
													<div class="col-md-4">
														<input type="text" id="exam-location" class="form-control" placeholder="Αίθουσα ή σύνδεσμος">
													</div>
												</div>
												<div class="text-end mt-3">
													<button class="btn btn-primary btn-sm" onclick="saveExamDetails()">Αποθήκευση Στοιχείων Εξέτασης</button>
												</div>
											</div>
                                        </div>
                                    </div>
                                </div>
                            `;
						break;
					case 'vathmologisi':
						contentContainer.innerHTML = `
                                <div class="card shadow-sm border-0 p-4">
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-clipboard-check fa-4x text-warning"></i>
                                            <h4 class="card-title text-warning fw-bold mt-3">Βαθμολόγηση σε Εξέλιξη</h4>
                                            <p class="card-text text-muted">
                                                Η τριμελής επιτροπή αξιολογεί τη δουλειά σας. Ολοκληρώστε τα παρακάτω βήματα:
                                            </p>
                                        </div>
                        
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <div class="card  shadow-sm">
                                                    <div class="card-body">
                                                        <h5 class="fw-bold mb-3">1. Δείτε το πρακτικό βαθμολόγησης</h5>
                                                        <p class="card-text text-muted">
                                                            Μπορείτε να δείτε το πρακτικό που δημιουργεί η τριμελής επιτροπή. Δεν είναι το τελικό.
                                                        </p>
															<a href="#" id="exam-report-button" class="list-group-item list-group-item-action text-success">
																<i class="fas fa-file-alt me-2"></i> Προβολή Πρακτικού Εξέτασης
															</a>
                                                    </div>
                                                </div>
                                            </div>
                        
                                            <div class="col-md-12">
                                                <div class="card  shadow-sm">
                                                    <div class="card-body">
                                                        <h5 class="fw-bold mb-3">2. Καταχώρηση συνδέσμου αποθετηρίου</h5>
                                                        <p class="card-text text-muted">
                                                            Εισάγετε το σύνδεσμο προς το αποθετήριο της βιβλιοθήκης (π.χ. Νημερτής), όπου βρίσκεται το τελικό κείμενο της διπλωματικής σας.
                                                        </p>
                                                        <div class="input-group mt-2">
                                                            <input type="text" id="repository-link" class="form-control" placeholder="Σύνδεσμος στο αποθετήριο">
                                                            <button class="btn btn-primary btn-sm" onclick="saveNemertesLink(${id_dipl})">
                                                                <i class="fas fa-save"></i> Καταχώρηση Συνδέσμου
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted text-center">
                                        <small><i class="fas fa-info-circle"></i> Η διαδικασία βαθμολόγησης ολοκληρώνεται μόνο αφού καταχωρηθούν οι βαθμοί από τους καθηγητές και ο σύνδεσμος του αποθετηρίου.</small>
                                    </div>
                                </div>
                            `;
							document.getElementById("exam-report-button").addEventListener("click", async function (e) {
								e.preventDefault();

								fetch(`../api/get_data_praktiko.php?id=${id_dipl}`)
									.then(response => response.json())
									.then(praktiko => {
										if (!praktiko.success) {
											showNotification("Πρόβλημα κατά τη δημιουργία του PDF.", "error");
											return;
										}

										const content = generatePDFContent(praktiko.details);

										const element = document.createElement("div");
										element.innerHTML = content;

										const options = {
											margin: 1,
											filename: `praktiko_eksetasis_${id_dipl}.pdf`,
											image: { type: 'jpeg', quality: 0.98 },
											html2canvas: { scale: 2 },
											jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' },
										};

										html2pdf().set(options).from(element).save();
									})
									.catch(error => {
										console.error(error);
										showNotification("Πρόβλημα κατά τη δημιουργία του PDF.", "error");
									});
								});


						break;
					case 'oloklirwmeni':
							contentContainer.innerHTML = `
								<div class="card shadow-sm border-0 p-4">
									<div class="card-body">
										<div class="text-center mb-4">
											<i class="fas fa-trophy fa-4x text-success"></i>
											<h4 class="card-title text-success fw-bold mt-3">Η διπλωματική σας έχει ολοκληρωθεί</h4>
											<p class="card-text text-muted">
												Διατηρείτε πρόσβαση στις πληροφορίες της διπλωματικής σας, στο ιστορικό αλλαγών κατάστασης και στο πρακτικό εξέτασης.
											</p>
										</div>
										<div class="row g-4">
											<div class="col-md-6">
												<div class="card border-light shadow-sm h-100">
													<div class="card-header  fw-bold">Πληροφορίες Διπλωματικής</div>
													<div class="card-body">
														<ul class="list-group">
															<li class="list-group-item">
																<strong>Τίτλος:</strong> <span id="thesis-title">-</span>
															</li>
															<li class="list-group-item">
																<strong>Περίληψη:</strong> <span id="thesis-summary">-</span>
															</li>
															<li class="list-group-item">
																<strong>Επιβλέπων:</strong> <span id="thesis-supervisor">-</span>
															</li>
														</ul>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="card border-light shadow-sm h-100">
													<div class="card-header  fw-bold">Αρχεία</div>
													<div class="card-body">
														<div class="list-group">
															<a href="#" id="topic-file-link" class="list-group-item list-group-item-action text-primary">
																<i class="fas fa-file-alt me-2"></i> Λήψη Αρχείου Θέματος
															</a>
															<a href="#" id="student-file-link" class="list-group-item list-group-item-action text-secondary">
																<i class="fas fa-file-alt me-2"></i> Λήψη Αρχείου Φοιτητή
															</a>
															<a href="#" id="exam-report-link" class="list-group-item list-group-item-action text-success">
																<i class="fas fa-file-alt me-2"></i> Προβολή Πρακτικού Εξέτασης
															</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="mt-5">
											<div class="card border-light shadow-sm">
												<div class="card-header  fw-bold">Ιστορικό Κατάστασης</div>
												<div class="card-body">
													<div id="status-history" class="list-group">
														<div class="list-group-item">Προς Ανάθεση: <span id="pros_anathesi_date">-</span></div>
														<div class="list-group-item">Προς Έγκριση: <span id="pros_egkrisi_date">-</span></div>
														<div class="list-group-item">Ενεργή: <span id="energi_date">-</span></div>
														<div class="list-group-item">Εξέταση: <span id="exetasi_date">-</span></div>
														<div class="list-group-item">Ολοκληρωμένη: <span id="oloklirwsi_date">-</span></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							`;
						
							fetch(`../api/get_thesis_details.php?id=${id_dipl}`)
								.then(response => response.json())
								.then(data => {
									if (data.success) {
										const details = data.details;

										document.getElementById("thesis-title").innerText = details.topic || "N/A";
										document.getElementById("thesis-summary").innerText = details.summary || "N/A";
										document.getElementById("thesis-supervisor").innerText = details.supervisor || "N/A";
										
										document.getElementById("topic-file-link").setAttribute("href", `../uploads/${thesis.proff_file || "-"}`);
										document.getElementById("topic-file-link").setAttribute("target", "_blank");

										document.getElementById("student-file-link").setAttribute("href", `../uploads/${thesis.student_file || "-"}`);
										document.getElementById("student-file-link").setAttribute("target", "_blank");
										//document.getElementById("exam-report-link").setAttribute("href", `../exam_reports/${id_dipl}.pdf`); an apothikeuetai kathe pdf 


										document.getElementById("pros_anathesi_date").innerText = details.selection_date || "-";
										document.getElementById("pros_egkrisi_date").innerText = details.thesis_requested || "-";
										document.getElementById("energi_date").innerText = details.start_date || "-";
										document.getElementById("exetasi_date").innerText = details.exam_date || "-";
										document.getElementById("oloklirwsi_date").innerText = details.completion_date || "-";

										document.getElementById("exam-report-link").addEventListener("click", async function (e) {
											e.preventDefault();

											fetch(`../api/get_data_praktiko.php?id=${id_dipl}`)
												.then(response => response.json())
												.then(praktiko => {
													if (!praktiko.success) {
														showNotification("Πρόβλημα κατά τη δημιουργία του PDF.", "error");
														return;
													}

													const content = generatePDFContent(praktiko.details);

													const element = document.createElement("div");
													element.innerHTML = content;

													const options = {
														margin: 1,
														filename: `praktiko_eksetasis_${id_dipl}.pdf`,
														image: { type: 'jpeg', quality: 0.98 },
														html2canvas: { scale: 2 },
														jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' },
													};

													html2pdf().set(options).from(element).save();
												})
												.catch(error => {
													console.error(error);
													showNotification("Πρόβλημα κατά τη δημιουργία του PDF.", "error");
												});

										});
									} else {
										console.error("Fetch error:", data.message);
										showNotification("Σφάλμα κατά τη φόρτωση των πληροφοριών της διπλωματικής.", "error");
									}
								})
								.catch(error => {
									console.error("Fetch error:", error);
									showNotification("Σφάλμα κατά τη φόρτωση των πληροφοριών της διπλωματικής.", "error");
								});

							break;
						
						
						
					default:
						contentContainer.innerHTML = `
                                <div class="alert alert-danger text-center">
                                    <h4>Άγνωστη κατάσταση διπλωματικής.</h4>
                                    <p>Παρακαλώ επικοινωνήστε με τη Γραμματεία.</p>
                                </div>
                            `;
						break;
				}
			} else {
				contentContainer.innerHTML = `
                    <div class="card shadow-sm border-0 p-4">
                        <div class="card-body text-center">
                            <div class="text-center mb-4">
                                <i class="fas fa-exclamation-circle fa-5x text-warning"></i>
                            </div>
                            <h4 class="card-title text-warning fw-bold mb-3">Δεν έχετε επιλέξει διπλωματική εργασία</h4>
                            <p class="card-text text-muted">
                                Επισκεφθείτε τη σελίδα <strong>Διαθέσιμα Θέματα</strong> από το μενού, για να επιλέξετε ένα από τα διαθέσιμα θέματα.
                            </p>
                        </div>
                    </div>
                `;
			}
		})
		.catch(error => {
			console.error('Σφάλμα:', error);
			contentContainer.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <h4>Σφάλμα κατά τη φόρτωση της διπλωματικής.</h4>
                        <p>Παρακαλώ δοκιμάστε ξανά αργότερα.</p>
                    </div>
                `;
		});
}
//upload kai save draft file name stou foithth
function uploadDraft(idDiplwmatikis) {
	const fileInput = document.getElementById('draft-upload');

	if (!idDiplwmatikis) {
		showNotification('Δεν έχει οριστεί id διπλωματικής.', 'error');
		return;
	}

	if (fileInput.files.length > 0) {
		const file = fileInput.files[0];
		console.log('Uploading draft file:', file.name); //debugggg

		const formData = new FormData();
		formData.append('upload-file', file);
		formData.append('id_diplwmatikis', idDiplwmatikis);

		fetch('../api/student_upload_file.php', {
				method: 'POST',
				body: formData,
			})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					showNotification('Το κείμενο αναρτήθηκε επιτυχώς! Οι καθηγητές μπορούν να το δουν.', 'success');
					console.log('File uploaded successfully:', data.filePath);
				} else {
					alert(data.message);
				}
			})
			.catch((error) => {
				console.error('Error uploading file:', error);
				showNotification('Σφάλμα κατά το ανέβασμα του αρχείου.', 'error');
			});
	} else {
		alert('Παρακαλώ επιλέξτε αρχείο.');
	}
}
//get ta links pou exei valei
function fetchExternalLinks(idDiplwmatikis) {
	fetch('../api/student_get_external_links.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				id_diplwmatikis: idDiplwmatikis,
			}),
		})
		.then((response) => response.json())
		.then((data) => {
			const linkContainer = document.getElementById('external-links-container');
			linkContainer.innerHTML = '';

			if (data.success && data.links.length > 0) {
				data.links.forEach((link) => {
					const newLink = document.createElement('div');
					newLink.className = 'mb-1';
					newLink.innerHTML = `
                            <a href="${link}" target="_blank">${link}</a> 
                            <button class="btn btn-danger btn-sm ms-2" onclick="deleteExternalLink(${idDiplwmatikis}, '${link}', this)">Διαγραφή</button>`;
					linkContainer.appendChild(newLink);
				});
			} else {
				const emptyMessage = document.createElement('p');
				emptyMessage.className = 'text-muted';
				emptyMessage.textContent = 'Δεν υπάρχουν συνδέσμοι.';
				linkContainer.appendChild(emptyMessage);
			}
		})
		.catch((error) => {
			console.error('Error fetching links:', error);
			showNotification('Σφάλμα κατά τη φόρτωση των συνδέσμων.', 'error');
		});
}
//add external link
function addExternalLink(idDiplwmatikis) {
	const linkInput = document.getElementById('external-link');
	const link = linkInput.value.trim();

	if (link) {
		fetch('../api/student_add_external_link.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					id_diplwmatikis: idDiplwmatikis,
					link: link,
				}),
			})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					showNotification('Ο σύνδεσμος αποθηκεύτηκε επιτυχώς!', 'success');
					linkInput.value = '';
					fetchExternalLinks(idDiplwmatikis);
				} else {
					showNotification(data.message, 'error');
				}
			})
			.catch((error) => {
				console.error('Error:', error);
				showNotification('Σφάλμα κατά την αποθήκευση του συνδέσμου.', 'error');
			});
	} else {
		showNotification('Παρακαλώ εισάγετε έγκυρο σύνδεσμο.', 'error');
	}
}
//delete external link
function deleteExternalLink(idDiplwmatikis, link, element) {
	fetch('../api/student_delete_external_link.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				id_diplwmatikis: idDiplwmatikis,
				link: link,
			}),
		})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				element.parentElement.remove();
				showNotification('Ο σύνδεσμος διαγράφηκε επιτυχώς!', 'success');
			} else {
				alert(data.message);
			}
		})
		.catch((error) => {
			console.error('Error deleting link:', error);
			showNotification('Σφάλμα κατά τη διαγραφή του συνδέσμου.', 'error');
		});
}
//kalei to save_exam_details.php gia na apothikeusei plhrofories gia thn eksetash
function saveExamDetails() {
    const date = document.getElementById('exam-date').value;
    const time = document.getElementById('exam-time').value;
    const location = document.getElementById('exam-location').value;

    if (!date || !time || !location) {
        showNotification('Παρακαλώ συμπληρώστε όλα τα πεδία.', 'error');
        return;
    }

    const examDateTime = `${date} ${time}`;

    fetch('../api/get_profile.php')
        .then((response) => response.json())
        .then((profileData) => {
            if (profileData.success && profileData.user.am) {
                const studentId = profileData.user.am;

                fetch('../api/get_student_thesis.php')
                    .then((response) => response.json())
                    .then((thesisData) => {
                        if (thesisData.success && thesisData.data.length > 0) {
                            const studentData = thesisData.data[0];
                            const fileUploadedDate = new Date(studentData.date_file_uploaded);
                            const thesisId = studentData.id;

                            const minExamDate = new Date(fileUploadedDate.setDate(fileUploadedDate.getDate() + 21)); //3 vdomades meta min
							const maxExamDate = new Date(fileUploadedDate.setDate(fileUploadedDate.getDate() + 84)); //3 mhnes meta max
                            
							if (new Date(examDateTime) < minExamDate) {
                                showNotification(
                                    'Η ημερομηνία εξέτασης πρέπει να είναι τουλάχιστον 3 εβδομάδες μετά την ημερομηνία που ανεβάσατε το πρόχειρο κείμενο.',
                                    'error'
                                );
                                return;
                            }

							if (new Date(examDateTime) > maxExamDate) {
                                showNotification(
                                    'Η ημερομηνία εξέτασης πρέπει να είναι το αργότερο 3 μήνες μετά την ημερομηνία που ανεβάσατε το πρόχειρο κείμενο.',
                                    'error'
                                );
                                return;
                            }

                            fetch('../api/save_exam_details.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    exam_date: examDateTime,
                                    exam_location: location,
                                    thesis_id: thesisId,
                                    student_id: studentId,
                                }),
                            })
                                .then((response) => response.json())
                                .then((saveData) => {
                                    if (saveData.success) {
                                        showNotification('Τα στοιχεία της εξέτασης αποθηκεύτηκαν.', 'success');
                                    } else {
                                        console.error('Σφάλμα κατά την αποθήκευση των στοιχείων: ' + saveData.message);
                                        showNotification('Σφάλμα κατά την αποθήκευση των στοιχείων εξέτασης.', 'error');
                                    }
                                })
                                .catch((error) => {
                                    console.error('Σφάλμα κατά την αποθήκευση:', error);
                                    showNotification('Σφάλμα κατά την αποθήκευση των στοιχείων εξέτασης.', 'error');
                                });
                        } else {
                            showNotification('Δεν βρέθηκαν δεδομένα για τη διπλωματική.', 'error');
                        }
                    })
                    .catch((error) => {
                        console.error('Σφάλμα κατά την ανάκτηση δεδομένων:', error);
                        showNotification('Σφάλμα κατά την ανάκτηση δεδομένων για τη διπλωματική.', 'error');
                    });
            } else {
                showNotification('Δεν βρέθηκε το AM του φοιτητή.', 'error');
            }
        })
        .catch((error) => {
            console.error('Σφάλμα κατά την ανάκτηση δεδομένων φοιτητή:', error);
            showNotification('Σφάλμα κατά την ανάκτηση δεδομένων φοιτητή.', 'error');
        });
}
//kalei to student_upload_nemertes.php gia na kanei update to nhmerths link sth vash
function saveNemertesLink(id) {
    const repositoryLink = document.getElementById('repository-link').value.trim();

    if (!repositoryLink) {
        alert('Παρακαλώ εισάγετε έγκυρο σύνδεσμο.');
        return;
    }

    const payload = {
        thesis_id: id,
        repository_link: repositoryLink
    };

    fetch('../api/student_upload_nemertes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Αποτυχία.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
				showNotification('Ο σύνδεσμος προς το αποθετήριο Νημερτής αποθηκεύτηκε επιτυχώς.', 'success');
            } else {
				showNotification('Αποτυχία αποθήκευσης του συνδέσμου.', 'error');
            }
        })
        .catch(error => {
            console.error('Σφάλμα:', error);
			showNotification('Προέκυψε σφάλμα. Παρακαλώ δοκιμάστε ξανά αργότερα.', 'error');
        });
}
//vreate to pdf. exei html pou gemizei fields praktika
function generatePDFContent(details) {
	const examDateTime = details.exam_date || "-";
	let examDate = "-";
	let examTime = "-";
	
	if (examDateTime.includes(" ")) {
		[examDate, examTime] = examDateTime.split(" ");
		//hmeromhnia se morfh dd/mm/yyyy
		if (examDate.includes("-")) {
			const [year, month, day] = examDate.split("-");
			examDate = `${day}/${month}/${year}`;
		}
		//ksilwma ta seconds
		if (examTime.includes(":")) {
			examTime = examTime.slice(0, 5);
		}
	}

    return `
        <div style="font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.4; padding: 20px; color: #333;">
		<div style="text-align: center; margin-bottom: 20px;">
                <img src="../images/logo_gr.png" alt="Logo" style="display: block; margin: 0 auto; width: 600px; height: auto;">
            </div>
			<br>
            <h2 style="text-align: center; font-size: 12pt; margin-bottom: 5px;">ΠΡΟΓΡΑΜΜΑ ΣΠΟΥΔΩΝ</h2>
            <h3 style="text-align: center; font-size: 11pt; margin-bottom: 10px;">«ΤΜΗΜΑΤΟΣ ΜΗΧΑΝΙΚΩΝ, ΗΛΕΚΤΡΟΝΙΚΩΝ ΥΠΟΛΟΓΙΣΤΩΝ ΚΑΙ ΠΛΗΡΟΦΟΡΙΚΗΣ»</h3>
            <h4 style="text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 10px;">ΠΡΑΚΤΙΚΟ ΣΥΝΕΔΡΙΑΣΗΣ</h4>
            <h5 style="text-align: center; font-size: 10pt; margin-bottom: 20px;">ΤΗΣ ΤΡΙΜΕΛΟΥΣ ΕΠΙΤΡΟΠΗΣ ΓΙΑ ΤΗΝ ΠΑΡΟΥΣΙΑΣΗ ΚΑΙ ΚΡΙΣΗ ΤΗΣ ΔΙΠΛΩΜΑΤΙΚΗΣ ΕΡΓΑΣΙΑΣ</h5>
            <p style="text-align: center; margin-bottom: 5px;">του/της φοιτητή/φοιτήτρια <strong>${details.student_name || "-"}</strong></p>
            <br>
			<p style="text-align: justify; margin-bottom: 5px;">
                Η συνεδρίαση πραγματοποιήθηκε στην αίθουσα <strong>${details.location || "-"}</strong>, ημέρα
                <strong>${examDate}</strong>, και ώρα <strong>${examTime}</strong>.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Στην συνεδρίαση είναι παρόντα τα μέλη της Τριμελούς Επιτροπής, κ.κ.:
            </p>
            <ol style="margin-left: 20px; margin-bottom: 10px;">
                <li>${details.supervisor || "-"}</li>
                <li>${details.member1 || "-"}</li>
                <li>${details.member2 || "-"}</li>
            </ol>
            <p style="text-align: justify; margin-bottom: 5px;">
                Οι οποίοι ορίσθηκαν από την Συνέλευση του ΤΜΗΥΠ, στην συνεδρίαση της με αριθμό πρωτοκόλλου
                <strong>${details.ar_prot || "-"}</strong>.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Ο/Η φοιτητής/φοιτήτρια <strong>${details.student_name || "-"}</strong> ανέπτυξε το θέμα της
                Διπλωματικής του/της Εργασίας, με τίτλο <strong>«${details.topic || "-"}»</strong>.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Στην συνέχεια υποβλήθηκαν ερωτήσεις στον υποψήφιο από τα μέλη της Τριμελούς Επιτροπής και
                τους άλλους παρευρισκόμενους, προκειμένου να διαμορφώσουν σαφή άποψη για το περιεχόμενο της
                εργασίας, για την επιστημονική συγκρότηση του μεταπτυχιακού φοιτητή.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Μετά το τέλος της ανάπτυξης της εργασίας του και των ερωτήσεων, ο υποψήφιος αποχωρεί.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Ο Επιβλέπων καθηγητής κ. <strong>${details.supervisor || "-"}</strong>, προτείνει στα μέλη της
                Τριμελούς Επιτροπής, να ψηφίσουν για το αν εγκρίνεται η διπλωματική εργασία του
                <strong>${details.student_name || "-"}</strong>.
            </p>
            <p style="text-align: justify; margin-bottom: 5px;">
                Υπέρ της εγκρίσεως της Διπλωματικής Εργασίας του φοιτητή <strong>${details.student_name || "-"}</strong>,
                επειδή θεωρούν επιστημονικά επαρκή και το περιεχόμενό της ανταποκρίνεται στο θέμα που του
                δόθηκε.
            </p>
			<br>
            <p style="text-align: justify; margin-bottom: 5px;">
                Μετά της έγκριση, ο εισηγητής κ. <strong>${details.supervisor || "-"}</strong>, προτείνει στα
                μέλη της Τριμελούς Επιτροπής, να απονεμηθεί στο/στη φοιτητή/τρια κ.
                <strong>${details.student_name || "-"}</strong> ο βαθμός <strong>${details.final_grade || "-"}</strong>.
            </p>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9pt; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000; padding: 5px;">ΟΝΟΜΑΤΕΠΩΝΥΜΟ</th>
                        <th style="border: 1px solid #000; padding: 5px;">ΒΑΘΜΟΣ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;">${details.supervisor || "-"}</td>
                        <td style="border: 1px solid #000; padding: 5px;">${details.prof1_final_grade || "-"}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;">${details.member1 || "-"}</td>
                        <td style="border: 1px solid #000; padding: 5px;">${details.prof2_final_grade || "-"}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;">${details.member2 || "-"}</td>
                        <td style="border: 1px solid #000; padding: 5px;">${details.prof3_final_grade || "-"}</td>
                    </tr>
                </tbody>
            </table>
			<br>
			<br>
            <p style="text-align: justify; margin-top: 10px;">
                Μετά την έγκριση και την απονομή του βαθμού <strong>${details.final_grade || "-"}</strong>, η Τριμελής
                Επιτροπή, προτείνει να προχωρήσει στην διαδικασία για να ανακηρύξει τον κ.
                <strong>${details.student_name || "-"}</strong>, σε διπλωματούχο του Προγράμματος Σπουδών του
                «ΤΜΗΜΑΤΟΣ ΜΗΧΑΝΙΚΩΝ, ΗΛΕΚΤΡΟΝΙΚΩΝ ΥΠΟΛΟΓΙΣΤΩΝ ΚΑΙ ΠΛΗΡΟΦΟΡΙΚΗΣ ΠΑΝΕΠΙΣΤΗΜΙΟΥ ΠΑΤΡΩΝ» και να
                του απονέμει το Δίπλωμα Μηχανικού Η/Υ το οποίο αναγνωρίζεται ως Ενιαίος Τίτλος Σπουδών
                Μεταπτυχιακού Επιπέδου.
            </p>
        </div>
    `;
}
