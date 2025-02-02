document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('search-thesis-btn').addEventListener('click', loadGrading);
    setupMenuNavigation();
    setupAithseisFilters();
    setupThesisFilter();
    setupStudentSearch('student-search', 'suggestions-list', 'student-id');
});

// *** MENU NAVIGATION *** \\

function setupMenuNavigation() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (event) {
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
                case 'requests':
                    loadRequests();
                    break;
                case 'grading':
                    loadGrading();
                    break;
                case 'view-theses':
                    loadTheses();
                    break;
                case 'insert-data':
                    break;
                default:
                    console.warn(`No loader defined for section: ${section}`);
            }
        });
    });

    const initialSection = document.querySelector('.nav-link[data-section]');
    if (initialSection) {
        initialSection.click();
    }
}




// *** FILTRA *** \\
//filtra gia aithseis
function setupAithseisFilters() {
    const statusFilter = document.getElementById('my-filter-status');
    const reasonFilter = document.getElementById('filter-reason');

    if (statusFilter && reasonFilter) {
        const filterChangeHandler = () => {
            const filterStatus = statusFilter.value;
            const filterReason = reasonFilter.value;

            const apiStatus = getApiStatusText(filterStatus);
            const apiReason = getApiReasonText(filterReason);

            loadRequests(apiStatus, apiReason);
        };

        statusFilter.addEventListener('change', filterChangeHandler);
        reasonFilter.addEventListener('change', filterChangeHandler);
    }
}
//filtra gia diplwmatikes
function setupThesisFilter() {
    const statusFilter = document.getElementById('oles-diplwmatikes-filter-status');

    if (statusFilter) {
        const filterChangeHandler = () => {
            const selectedStatus = statusFilter.value;
            loadTheses(selectedStatus);
        };

        statusFilter.addEventListener('change', filterChangeHandler);
    }
}



// *** AITHSEIS *** \\
//load and show oles tis aithseis, filtering here
function loadRequests(filterStatus = 'pending', filterReason = 'all', currentPage = 1) {
    const tableBody = document.querySelector('#requests-table tbody');
    const paginationContainer = document.querySelector('#pagination-container');
    const itemsPerPage = 7;

    const fetchApi = `../api/secretary/get_aithseis.php?status=${filterStatus}&reason=${filterReason}`;

    fetch(fetchApi)
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (data.success && data.data.length > 0) {
                const allRequests = data.data;
                const totalRequests = allRequests.length;
                const totalPages = Math.ceil(totalRequests / itemsPerPage);
                const startIndex = (currentPage - 1) * itemsPerPage;
                const requestsToShow = allRequests.slice(startIndex, startIndex + itemsPerPage);

                requestsToShow.forEach(request => {
                    const row = `
                        <tr>
                            <td>${request.id}</td>
                            <td>${request.applicant_name} ${request.applicant_surname}</td>
                            <td>${translateReason(request.aithsh_gia)}</td>
                            <td>${request.id_diplwmatikis}</td>
                            <td>${request.date_requested}</td>
                            <td>${translateStatus(request.apanthsh)}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="openRequestModal(${request.id})">Προβολή</button>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });

                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        const pageItem = `
                            <li class="page-item ${activeClass}">
                                <button class="page-link" onclick="loadRequests('${filterStatus}', '${filterReason}', ${i})">${i}</button>
                            </li>`;
                        paginationContainer.insertAdjacentHTML('beforeend', pageItem);
                    }
                }
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">Δεν βρέθηκαν δεδομένα.</td>
                    </tr>`;
            }
        })
        .catch(error => {
            console.error('Σφάλμα:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">Σφάλμα φόρτωσης δεδομένων.</td>
                </tr>`;
        });
}
//view modal gia kathe aithsh, kaleitai apo loadRequests
function openRequestModal(request_id) {
    document.getElementById('diplomaTopic').textContent = 'Μη διαθέσιμο';
    document.getElementById('description').textContent = 'Μη διαθέσιμο';
    document.getElementById('student').textContent = 'Μη διαθέσιμο';
    document.getElementById('supervisor').textContent = 'Μη διαθέσιμο';
    document.getElementById('member2').textContent = 'Μη διαθέσιμο';
    document.getElementById('member3').textContent = 'Μη διαθέσιμο';
    document.getElementById('requestDate').textContent = 'Μη διαθέσιμο';
    const requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
    const attachmentLink = document.getElementById('attachmentLink');
    attachmentLink.href = '#';
    attachmentLink.textContent = 'Δεν υπάρχει αρχείο';

    const modalFooter = document.querySelector('.modal-footer');
    modalFooter.innerHTML = '';

    fetch(`../api/secretary/get_aithsh_details.php?id=${request_id}`)
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                showNotification('Δεν βρέθηκαν δεδομένα για αυτή την αίτηση.', 'error');
                return;
            }

            const details = data.details;

            document.getElementById('diplomaTopic').textContent = details.thesis?.topic || 'Μη διαθέσιμο';
            document.getElementById('description').textContent = details.thesis?.summary || 'Μη διαθέσιμο';
            document.getElementById('student').textContent =
                details.student
                    ? `${details.student} (${details.student_am || 'Μη διαθέσιμο'})`
                    : 'Μη διαθέσιμο';
            document.getElementById('supervisor').textContent = details.supervisor || 'Μη διαθέσιμο';
            document.getElementById('member2').textContent = details.member2 || 'Μη διαθέσιμο';
            document.getElementById('member3').textContent = details.member3 || 'Μη διαθέσιμο';
            document.getElementById('requestDate').textContent = `Ημερομηνία Αίτησης: ${details.request_date || 'Μη διαθέσιμο'}`;
            document.getElementById('thesesCreationDate').textContent = details.thesis.creation_date || 'Μη διαθέσιμο';
            
            if (details.thesis?.file_name) {
                attachmentLink.href = details.thesis.file_name;
                attachmentLink.textContent = 'Λήψη Αρχείου';
            }

            const modalTitle = document.getElementById('requestModalLabel');

            modalTitle.textContent= "Αίτηση " + getReasonDescription(details.request_reason);


            if (details.request_status === 'pending') {
                if (details.request_reason === 'pros_egrisi_oloklirwmenh') {
                    const gradingButton = document.createElement('button');
                    gradingButton.classList.add('btn', 'btn-sm', 'btn-secondary');
                    gradingButton.textContent = 'Μετάβαση στο βαθμολόγιο';
                    gradingButton.onclick = () => {
                        const thesisId = details.thesis.id_dipl;
                        if (thesisId) {
                            modalFooter.innerHTML = '';
                            document.querySelector('[data-section="grading"]').click();
                            loadGrading(thesisId);
                            openGradingModal(thesisId);
                            
                            const approveButton = document.createElement('button');
                            approveButton.className = 'btn btn-success';
                            approveButton.textContent = 'Έγκριση';
                            approveButton.onclick = () =>
                            handleRequestAction(request_id, 'approve', details.request_reason);
            
                            const rejectButton = document.createElement('button');
                            rejectButton.className = 'btn btn-danger';
                            rejectButton.textContent = 'Απόρριψη';
                            rejectButton.onclick = () =>
                            handleRequestAction(request_id, 'reject', details.request_reason);
            
                            modalFooter.appendChild(rejectButton);
                            modalFooter.appendChild(approveButton);
                        } else {
                            showNotification('Δεν βρέθηκε το ID διπλωματικής εργασίας.', 'error');
                        }
                    };
                    const modalHeader = document.querySelector('.modal-header');
                    modalHeader.appendChild(gradingButton);
                } else {
                    const approveButton = document.createElement('button');
                    approveButton.className = 'btn btn-success';
                    approveButton.textContent = 'Έγκριση';
                    approveButton.onclick = () =>
                    handleRequestAction(request_id, 'approve', details.request_reason);
            
                    const rejectButton = document.createElement('button');
                    rejectButton.className = 'btn btn-danger';
                    rejectButton.textContent = 'Απόρριψη';
                    rejectButton.onclick = () =>
                    handleRequestAction(request_id, 'reject', details.request_reason);
            
                    modalFooter.appendChild(rejectButton);
                    modalFooter.appendChild(approveButton);
                }
            } else if (details.request_status === 'accepted') {
                modalFooter.innerHTML = `<span class="text-danger fw-bold">Η ΑΙΤΗΣΗ ΕΧΕΙ ΕΓΚΡΙΘΕΙ ΜΕ ΑΡΙΘΜΟ ΠΡΩΤΟΚΟΛΛΟΥ: ${details.protocol_number}</span>`;
            } else if (details.request_status === 'denied') {
                modalFooter.innerHTML = `<span class="text-danger fw-bold">Η ΑΙΤΗΣΗ ΕΧΕΙ ΑΠΟΡΡΙΦΘΕΙ ΜΕ ΑΡΙΘΜΟ ΠΡΩΤΟΚΟΛΛΟΥ: ${details.protocol_number}</span>`;
            }


            requestModal.show();
        })
        .catch((error) => {
            console.error('Σφάλμα κατά τη φόρτωση των δεδομένων:', error);
            showNotification('Παρουσιάστηκε σφάλμα κατά τη φόρτωση των δεδομένων.', 'error');
        });
}
//koumpia egkrish aporipsh
function handleRequestAction(request_id, action, reason) {
    const protocolModal = document.getElementById('protocolModal');
    protocolModal.dataset.requestId = request_id;
    protocolModal.dataset.action = action;
    protocolModal.dataset.reason = reason;

    const modalTitle = document.getElementById('protocolModalLabel');
    modalTitle.textContent =
        action === 'approve'
            ? 'Έγκριση '+ getReasonDescription(reason)
            : 'Απόρριψη '+ getReasonDescription(reason);

    document.getElementById('protocolNumber').value = '';

    const bootstrapModal = new bootstrap.Modal(protocolModal);
    bootstrapModal.show();
}
//send protocol num klp
document.getElementById('saveProtocolButton').addEventListener('click', () => {
    const protocolModal = document.getElementById('protocolModal');
    const requestId = protocolModal.dataset.requestId;
    const action = protocolModal.dataset.action;
    const reason = protocolModal.dataset.reason;
    const protocolNumber = document.getElementById('protocolNumber').value;
    const comment = document.getElementById('comment').value;

    if (!protocolNumber) {
        showNotification('Παρακαλώ εισάγετε αριθμό πρωτοκόλλου.', 'error');
        return;
    }

    const apiEndpoint =
    reason === 'pros_egrisi_energi'
        ? action === 'approve'
            ? '../api/secretary/request_egrisi_approve.php'
            : '../api/secretary/request_egrisi_deny.php'
    : reason === 'pros_egrisi_oloklirwmenh'
        ? action === 'approve'
            ? '../api/secretary/request_oloklirwmenh_approve.php'
            : '../api/secretary/request_oloklirwmenh_deny.php'
    : reason === 'pros_egrisi_akurwshs'
        ? action === 'approve'
            ? '../api/secretary/request_akurwsh_approve.php'
            : '../api/secretary/request_akurwsh_deny.php'
    : reason === 'pros_egrisi_allagis_f'
        ? action === 'approve'
                ? '../api/secretary/request_allaghF_approve.php'
                : '../api/secretary/request_allaghF_deny.php'
    : null;
    


    fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            request_id: requestId,
            protocol_number: protocolNumber,
            comment: comment
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showNotification('Επιτυχής καταχώρηση απάντησης!', 'success');

                const bootstrapModal = bootstrap.Modal.getInstance(protocolModal);
                bootstrapModal.hide();
                loadRequests();
                openRequestModal(requestId);
            } else {
                showNotification('Αποτυχία ενημέρωσης: ' + data.message, 'error');
            }
        })
        .catch((error) => {
            console.error('Σφάλμα αποστολής:', error);
            showNotification('Παρουσιάστηκε σφάλμα κατά την αποστολή των δεδομένων.', 'error');
        });
});


// *** VATHMOLOGISI *** \\
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
                .catch(() => alert('Σφάλμα κατά την αναζήτηση φοιτητών.'));
        }
    });

    document.addEventListener('click', function(e) {
        if (!studentSearchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });
}
//load tous vathmous sto modal
function loadGrading(thesisId = null) {
    const studentId = document.getElementById('student-id').value;
    const tableBody = document.querySelector('#grading tbody');

    let url = thesisId 
        ? `../api/secretary/get_grades_filtered.php?id=${thesisId}` 
        : '../api/secretary/get_grades_filtered.php';

    if (studentId) {
        url += (url.includes('?') ? '&' : '?') + `am_foititi=${studentId}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const gradingData = data.data;
                tableBody.innerHTML = '';

                gradingData.forEach(grading => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${grading.prof1_name}</td>
                        <td>${grading.id_diplwmatikis}</td>
                        <td>${grading.prof1_name}</td>
                        <td>${grading.prof2_name}</td>
                        <td>${grading.prof3_name}</td>
                        <td>${grading.date_requested}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openGradingModal(${grading.id_diplwmatikis})">Προβολή</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">Δεν βρέθηκαν δεδομένα.</td>
                    </tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Σφάλμα κατά την φόρτωση των δεδομένων.', 'error');
        });
}
//modal vathmologiou
function openGradingModal(thesisId) {
    const thesisGradesUrl = `../api/get_grades.php?id=${thesisId}`;
    const thesisDetailsUrl = `../api/get_thesis_details.php?id=${thesisId}`;
    const modalElement = document.getElementById('gradingModal');


    Promise.all([
        fetch(thesisGradesUrl).then(response => response.json()),
        fetch(thesisDetailsUrl).then(response => response.json())
    ])
    .then(([gradingData, detailsData]) => {
        if (gradingData.success && detailsData.success) {
            const grades = gradingData.data || {};
            const vathm_details = detailsData.details || {};

            document.getElementById('vathm-thesis-summary').innerText = vathm_details.summary || 'N/A';
            document.getElementById('vathm-thesis-student').innerText = vathm_details.student || 'N/A';
            document.getElementById('vathm-thesis-supervisor').innerText = vathm_details.supervisor || 'N/A';
            document.getElementById('vathm-member2_').innerText = vathm_details.member2 || 'N/A';
            document.getElementById('vathm-member3_').innerText = vathm_details.member3 || 'N/A';
            document.getElementById('vathm-thesis-exam-date').innerText = vathm_details.exam_date || 'N/A';
            document.getElementById('vathm-thesis-grading-date').innerText = grades.date_requested || 'N/A';
            document.getElementById('vathm-thesis-completion-date').innerText = vathm_details.completion_date || 'N/A';
           
            document.getElementById('final_grade').innerText = grades.final_grade || 'N/A';
            document.getElementById('vathm-thesis-topic-header').innerText = vathm_details.topic || 'N/A';

            document.getElementById('prof1-name').innerText = grades.prof1_name || 'N/A';
            document.getElementById('prof1-grade-1').innerText = grades.prof1_grade_crit_1 || '--';
            document.getElementById('prof1-grade-2').innerText = grades.prof1_grade_crit_2 || '--';
            document.getElementById('prof1-grade-3').innerText = grades.prof1_grade_crit_3 || '--';
            document.getElementById('prof1-grade-4').innerText = grades.prof1_grade_crit_4 || '--';
            document.getElementById('prof1-final-grade').innerText = grades.prof1_final_grade || '--';

            document.getElementById('prof2-name').innerText = grades.prof2_name || 'N/A';
            document.getElementById('prof2-grade-1').innerText = grades.prof2_grade_crit_1 || '--';
            document.getElementById('prof2-grade-2').innerText = grades.prof2_grade_crit_2 || '--';
            document.getElementById('prof2-grade-3').innerText = grades.prof2_grade_crit_3 || '--';
            document.getElementById('prof2-grade-4').innerText = grades.prof2_grade_crit_4 || '--';
            document.getElementById('prof2-final-grade').innerText = grades.prof2_final_grade || '--';

            document.getElementById('prof3-name').innerText = grades.prof3_name || 'N/A';
            document.getElementById('prof3-grade-1').innerText = grades.prof3_grade_crit_1 || '--';
            document.getElementById('prof3-grade-2').innerText = grades.prof3_grade_crit_2 || '--';
            document.getElementById('prof3-grade-3').innerText = grades.prof3_grade_crit_3 || '--';
            document.getElementById('prof3-grade-4').innerText = grades.prof3_grade_crit_4 || '--';
            document.getElementById('prof3-final-grade').innerText = grades.prof3_final_grade || '--';



            
            const fileName = detailsData.file_name || '';
            const thesisFileLinkElement = document.getElementById('thesis-file-name');
            if (fileName === 'N/A' || fileName === '') {
                 thesisFileLinkElement.textContent = 'Δεν υπάρχει αρχείο.';
             } else {
                 thesisFileLinkElement.innerHTML = `<a href="../uploads/${fileName}" target="_blank">${fileName}</a>`;
            }

            const steps = document.querySelectorAll('.vathm-progress-timeline .step');

            steps.forEach((step) => {
                step.classList.remove('completed', 'active', 'clickable');
                step.style.backgroundColor = "";
            });

            //console.log(vathm_details.status); //debugggg
            switch (vathm_details.status) {
                
                case 'exetasi':
                    resetProgressModal();
                    const stepsExetasi = document.querySelectorAll('.vathm-progress-timeline .step');
                    cancelAssignmentContainer.innerHTML = '';
                    
                    if(details.exam_date_pass = true){
                        updateSteps(stepsExetasi, 1, 0);                        
                        document.getElementById('status-info').innerText ='Εκκρεμεί εξέταση του φοιτητή από την Τριμελή επιτροπή. Πληροφορίες εξέτασης βρίσκονται παρακάτω.';
                    }else if (details.exam_date_pass = false){
                        updateSteps(stepsExetasi, 1, 1);
                        document.getElementById('status-info').innerText ='Η εξέταση έχει ολοκληρωθεί. Εκκρεμεί η αλλαγή κατάστασης από τον καθηγητή για την ενεργοποίηση της βαθμολόγησης.';
                    }
                    break;
                case 'vathmologisi':
                    resetProgressModal();    
                    const stepsVathmologisi = document.querySelectorAll('.vathm-progress-timeline .step');
                                       
                    if(grades.prof1_grade_crit_1 && grades.prof1_grade_crit_2 && grades.prof1_grade_crit_3 && grades.prof2_grade_crit_1 && grades.prof2_grade_crit_2 && grades.prof2_grade_crit_3 && grades.prof3_grade_crit_1 && grades.prof3_grade_crit_2 && grades.prof3_grade_crit_3){
                        updateSteps(stepsVathmologisi, 1, 1);
                        document.getElementById('vathm-status-info').innerText ='Οι βαθμολογητές έχουν ολοκληρώσει την βαθμολόγηση. Ελέγξτε τις αιτήσεις.';
                    }else{        
                        updateSteps(stepsVathmologisi, 2, 2);
                        document.getElementById('vathm-status-info').innerText ='Οι βαθμολογητές εισάγουν τις βαθμολογίες τους. Σύντομα θα πρέπει να τις εγκρίνετε.';
                    }
                    break;
                case 'oloklirwmeni':
                    resetProgressModal();  
                    const stepsOloklirwmeni = document.querySelectorAll('.vathm-progress-timeline .step');
                    updateSteps(stepsOloklirwmeni, 2, -1);
                    document.getElementById('vathm-status-info').innerText = 'Το θέμα έχει ολοκληρωθεί. Παρακάτω φαίνονται όλες οι πληροφορίες του θέματος.';
                    break;
                case 'akurwmeni':
                    document.getElementById('vathm-status-info').innerText = 'Το θέμα έχει ακυρωθεί. Πληροφορίες παρακάτω.';
                    break;
            }

            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            showNotification('Σφάλμα κατά τη φόρτωση βαθμολογίας ή λεπτομερειών.', 'error');
        }
    })
    .catch(() => {
        showNotification('Σφάλμα κατά τη φόρτωση των δεδομένων.', 'error');
    });
}

// *** THESIS *** \\
//load and show oles tis diplwmatikes, filtering here
function loadTheses(filterStatus = 'all', currentPage = 1) {
    const tableBody = document.querySelector('#my-theses-table-body');
    const paginationContainer = document.querySelector('#pagination-container-diplomatikes');
    const itemsPerPage = 7;

    const fetchApi = `../api/secretary/get_all_theses.php?status=${filterStatus}`;

    fetch(fetchApi)
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = '';
            paginationContainer.innerHTML = '';

            if (data.success && data.data.length > 0) {
                const filteredTheses = data.data;
                const totalTheses = filteredTheses.length;
                const totalPages = Math.ceil(totalTheses / itemsPerPage);
                const startIndex = (currentPage - 1) * itemsPerPage;
                const thesesToShow = filteredTheses.slice(startIndex, startIndex + itemsPerPage);

                thesesToShow.forEach(thesis => {
                    const studentInfo = thesis.student_name
                        ? `${thesis.student_name} (${thesis.am_foititi})`
                        : 'Δεν έχει ανατεθεί';

                    const row = `
                        <tr>
                            <td>${thesis.id}</td>
                            <td>${thesis.topic}</td>
                            <td>${studentInfo}</td>
                            <td>${translateStatus(thesis.status)}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="openThesisModal(${thesis.id})">Προβολή</button>
                            </td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });

                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        const activeClass = i === currentPage ? 'active' : '';
                        const pageItem = `
                            <li class="page-item ${activeClass}">
                                <button class="page-link" onclick="loadTheses('${filterStatus}', ${i})">${i}</button>
                            </li>`;
                        paginationContainer.insertAdjacentHTML('beforeend', pageItem);
                    }
                }
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Δεν βρέθηκαν διπλωματικές.</td>
                    </tr>`;
            }
        })
        .catch(error => {
            console.error('Σφάλμα:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">Σφάλμα φόρτωσης δεδομένων.</td>
                </tr>`;
        });
}
//view modal gia kathe diplwmatiki, kaleitai apo loadTheses
function openThesisModal(thesisId) {
    const thesisDetailsUrl = `../api/get_thesis_details.php?id=${thesisId}`;
    const thesisGradesUrl = `../api/get_grades.php?id=${thesisId}`;
    const examDetailsUrl = `../api/get_exam_details.php?id_diplwmatikis=${thesisId}`;

    const cancelAssignmentContainer = document.getElementById('cancel-assignment-container'); //savarei to koumpi gia akyrwsh anatheshs na mhn emfanizetai polles fores kai na exei swsto id
    if (cancelAssignmentContainer) {
        cancelAssignmentContainer.innerHTML = '';
    }
    const modalElement = document.getElementById('thesisModal');

    Promise.all([
        fetch(thesisGradesUrl).then(response => response.json()),
        fetch(thesisDetailsUrl).then(response => response.json()),
        fetch(examDetailsUrl).then(response => response.json())
    ])
    .then(([gradingData, detailsData, examData]) => {
        if (detailsData.success && gradingData.success && examData.success) {
            const details = detailsData.details || {};
            const grades = gradingData.data || {};
            
            const exam = examData.announcements && examData.announcements[0] ? examData.announcements[0] : {};
            const StudentfileName = examData.file_name || '';
            const thesisStudentFileLinkElement = document.getElementById('student-thesis-file-name');
            const timeSinceAssignment = document.getElementById('time-since-assignment');
            
            document.getElementById('thesis-topic-header').innerText = details.topic || 'N/A';
            document.getElementById('thesis-summary').innerText = details.summary || 'N/A';
            document.getElementById('thesis-student').innerText = details.student || 'N/A';
            document.getElementById('thesis-supervisor').innerText = details.supervisor || 'N/A';
            document.getElementById('member2_').innerText = details.member2 || 'N/A';
            document.getElementById('member3_').innerText = details.member3 || 'N/A';
            document.getElementById('thesis-creation-date').innerText = details.creation_date || 'N/A';
            document.getElementById('thesis-selection-date').innerText = details.selection_date || 'N/A';
            document.getElementById('thesis-start-date').innerText = details.start_date || 'N/A';
            document.getElementById('thesis-exam-date').innerText = details.exam_date || 'N/A';
            document.getElementById('thesis-requested-date').innerText = details.thesis_requested || 'N/A';
            document.getElementById('thesis-grading-date').innerText = grades.date_requested || 'N/A';
            document.getElementById('thesis-completion-date').innerText = details.completion_date || 'N/A';
            document.getElementById('_nemertes_link').innerText = details.nemertes_link || 'N/A';
            
            
            document.getElementById('_exam-date').innerText = exam.exam_date || 'N/A';
            document.getElementById('_exam-location').innerText = exam._location || 'N/A';
    
            console.log(details.raw_start_date); //debugggg 
            if(details.status != 'pros_anathesi' || details.status != 'diathesimi' || details.status != 'pros_egrisi'){
                if (details.raw_start_date) {
                    const startDate = new Date(details.raw_start_date.replace(' ', 'T'));
                    startAutoUpdatingCounter(startDate);
                } else {
                    timeSinceAssignment.textContent = 'Η ανάθεση δεν έχει εγκριθεί.';
                }
            } else {
                timeSinceAssignment.textContent = '-';
            }


            //arxeio foithth
            if (thesisStudentFileLinkElement) {
                if (StudentfileName && StudentfileName !== 'N/A') {
                    thesisStudentFileLinkElement.innerHTML = `<a href="../uploads/${StudentfileName}" target="_blank">${StudentfileName}</a>`;
                } else {
                    thesisStudentFileLinkElement.innerHTML = '<span>Δεν υπάρχει αρχείο</span>';
                }
            }   

            //arxeio kathigiti
            const fileName = details.file_name || '';
            const thesisFileLinkElement = document.getElementById('thesis-file-name');
            
            if (thesisFileLinkElement) {
                if (fileName && fileName !== 'N/A') {
                    thesisFileLinkElement.innerHTML = `<a href="../uploads/${fileName}" target="_blank">${fileName}</a>`;
                } else {
                    thesisFileLinkElement.innerHTML = '<span>Δεν υπάρχει αρχείο</span>';
                }
            }
                

                const steps = document.querySelectorAll('.progress-timeline .step');

                steps.forEach((step) => {
                    step.classList.remove('completed', 'active', 'clickable');
                    step.style.backgroundColor = "";
                });
                


                
                switch (details.status) {
                    case 'diathesimi':
                        resetProgressModal();
                        const stepsDiathesimi = document.querySelectorAll('.progress-timeline .step');
                        updateSteps(stepsDiathesimi, 0, 0);

                        cancelAssignmentContainer.innerHTML = '';
                        document.getElementById('status-info').innerText = 'Το θέμα είναι διαθέσιμο για επιλογή από φοιτητή.';
                        break;
                    case 'pros_anathesi':
                        resetProgressModal();
                        const stepsAnathesi = document.querySelectorAll('.progress-timeline .step');
                        updateSteps(stepsAnathesi, 1, 1);

                        cancelAssignmentContainer.innerHTML = '';
                        document.getElementById('status-info').innerText = 'Το θέμα έχει επιλεχθεί από φοιτητή.';
                        break;
                    case 'pros_egrisi':
                        const stepsEgrisi = document.querySelectorAll('.progress-timeline .step');
                        resetProgressModal();
                        updateSteps(stepsEgrisi, 2, 2);

                        cancelAssignmentContainer.innerHTML = '';
                        document.getElementById('status-info').innerText = 'Εκκρεμεί απάντηση. Ελέγξτε τις αιτήσεις.';
                        break;
                    case 'energi':
                        const stepsEnergi = document.querySelectorAll('.progress-timeline .step');
                        resetProgressModal();
                        updateSteps(stepsEnergi, 3, 3);

                        document.getElementById('status-info').innerText = 'Η εργασία εκπονείται.';
                        
                        const cancelAssignmentButton = document.createElement('button');
                        cancelAssignmentButton.id = 'cancel-assignment-btn';
                        cancelAssignmentButton.className = 'btn btn-danger bt px-4 py-2';
                        cancelAssignmentButton.textContent = 'Αίτηση Ακύρωσης Ανάθεσης';
                        cancelAssignmentButton.onclick = () => {
                            showDenyConfirmationModal(
                                'Επιβεβαίωση Ακύρωσης',
                                'Είστε σίγουροι ότι θέλετε να σταλεί αίτηση ακύρωσης ανάθεσης της διπλωματικής από τον φοιτητή;',
                                '',
                                () => cancelAssignment(thesisId)
                            );
                        };
                        cancelAssignmentContainer.appendChild(cancelAssignmentButton);
                        break;
                    case 'exetasi':
                        const stepsExetasi = document.querySelectorAll('.progress-timeline .step');
                        cancelAssignmentContainer.innerHTML = '';
                        resetProgressModal();
                        if(details.exam_date_pass = true){
                            updateSteps(stepsExetasi, 3, 4);                        
                            document.getElementById('status-info').innerText ='Εκκρεμεί εξέταση του φοιτητή από την Τριμελή επιτροπή. Πληροφορίες εξέτασης βρίσκονται παρακάτω.';
                        }else if (details.exam_date_pass = false){
                            updateSteps(stepsExetasi, 4, 4);
                            document.getElementById('status-info').innerText ='Η εξέταση έχει ολοκληρωθεί. Εκκρεμεί η αλλαγή κατάστασης από τον καθηγητή για την ενεργοποίηση της βαθμολόγησης.';
                        }
                        break;
                    case 'vathmologisi':
                        if(grades.date_requested){
                            const stepsVathmologisi = document.querySelectorAll('.progress-timeline .step');
                            resetProgressModal();
                            updateSteps(stepsVathmologisi, 5, 5);
                            cancelAssignmentContainer.innerHTML = '';
                            document.getElementById('status-info').innerText ='Οι βαθμολογητές έχουν ολοκληρώσει την βαθμολόγηση. Ελέγξτε τις αιτήσεις.';
                        } else{
                            const stepsVathmologisi = document.querySelectorAll('.progress-timeline .step');
                            resetProgressModal();
                            updateSteps(stepsVathmologisi, 4, 5);
                            cancelAssignmentContainer.innerHTML = '';
                            document.getElementById('status-info').innerText ='Οι βαθμολογητές εισάγουν τις βαθμολογίες τους. Σύντομα θα πρέπει να τις εγκρίνετε.';
                        }

                        break;
                    case 'oloklirwmeni':
                        const stepsOloklirwmeni = document.querySelectorAll('.progress-timeline .step');
                        resetProgressModal();
                        updateSteps(stepsOloklirwmeni, 7, -1);

                        cancelAssignmentContainer.innerHTML = '';
                        document.getElementById('status-info').innerText = 'Το θέμα έχει ολοκληρωθεί. Παρακάτω φαίνονται όλες οι πληροφορίες του θέματος.';
                        break;
                    case 'akurwmeni':
                        resetProgressModal();
                        cancelAssignmentContainer.innerHTML = '';
                        document.getElementById('status-info').innerText = 'Το θέμα έχει ακυρωθεί. Πληροφορίες παρακάτω.';
                        break;
                }
               
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
//timer function apo anathesh, opws ston foithth
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
			document.getElementById('time-since-assignment').textContent = 'Η ανάθεση δεν έχει εγκριθεί.';
		}
	}

	updateCounter();
	setInterval(updateCounter, 1000);
}
// *** MAPPING *** \\
//map gia filtra aithsewn apo html se api
function getApiStatusText(status) {
    const statusMapping = {
        all: 'all',
        pending: 'pending',
        accepted: 'accepted',
        denied: 'denied'
    };
    return statusMapping[status] || 'Άγνωστο getApiStatusText';
}
//de thumamai giati uparxei auto
function getApiReasonText(reason) {
    const reasonMapping = {
        all: 'all',
        enarksi: 'pros_egrisi_energi',
        cancel: 'pros_egrisi_akurwshs',
        complete: 'pros_egrisi_oloklirwmeni'
    };
    return reasonMapping[reason] || 'Άγνωστο getApiReasonText';
}
//map gia emfanish aithshs apo api se html (aithseis table)
function translateReason(reason) {
    const reasonMapping = {
        pros_egrisi_energi: 'Έναρξη εκπόνησης',
        pros_egrisi_oloklirwmenh: 'Ολοκλήρωση',
        pros_egrisi_akurwshs: 'Ακύρωση',
        pros_egrisi_allagis_f: "Αλλαγή φοιτητή",
        all: 'Όλα'
    };
    return reasonMapping[reason] || 'Άγνωστο translateReason';
}
//map gia emfanish apanthshs aithshs apo api se html (aithseis table)
function translateStatus(status) {
    const statusMapping = {
        pending: 'Εκκρεμεί',
        accepted: 'Αποδεκτή',
        denied: 'Απορρίφθηκε',
        akurwmeni: 'Ακυρωμένη',
        energi: 'Ενεργή',
        oloklirwmeni: 'Ολοκληρωμένη',
        diathesimi: 'Διαθέσιμη',
        exetasi: 'Εξέταση',
        vathmologisi: 'Βαθμολόγηση',
        pros_anathesi: 'Προς ανάθεση',
        pros_egrisi: 'Προς έγκριση',
        all: 'Όλες'
    };
    return statusMapping[status] || 'Άγνωστο translateStatus';
}
//map gia ton titlo tou modal aithshs
function getReasonDescription(reason) {
    const descriptions = {
        pros_egrisi_energi: 'Έναρξης Εκπόνησης',
        pros_egrisi_akurwshs: 'Ακύρωσης',
        pros_egrisi_allagis_f: 'Αλλαγής Φοιτητή',
        pros_egrisi_oloklirwmenh: 'Ολοκλήρωσης',
    };
    return descriptions[reason] || 'Άγνωστο getReasonDescription';
}

// *** MISCELLANEOUS *** \\
//modal epivevaiwshs epikindunhs energeias
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
//stelnei aithsh akurwshs ANATHESHS tou thematos ston foithth san na to esteile o foithths
function cancelAssignment(thesisId) {
    fetch('../api/secretary/cancel_assignment.php', {
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
//reset to progress bart
function resetProgressModal() {
    const steps = document.querySelectorAll('.progress-timeline .step');
    steps.forEach((step) => {
        step.classList.remove('completed', 'active', 'clickable');
        step.style.backgroundColor = '';
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


// *** INSERT USERS DATA *** \\
//import data apo to usidas
function get_data_usidas() {
    fetch('../api/insert_data_usidas.php', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(result => {
        showNotification('Επιτυχής εισαγωγή χρηστών', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Υπήρξε πρόβλημα με την εισαγωγή των δεδομένων!', 'error');
    });
}
// anoigei file picker gia json upload
function openFilePicker() {
    const fileInput = document.getElementById('json-file');
    if (fileInput) {
        fileInput.click();
    }
}

function openFileUploadModal() {
    const fileUploadModal = new bootstrap.Modal(document.getElementById('fileUploadModal'));
    fileUploadModal.show();
}

function handleModalFileUpload() {
    const fileInput = document.getElementById('json-file-modal');
    const feedback = document.getElementById('upload-feedback');

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('jsonFile', file);

    fetch('../api/insert_data_json.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.code === 1) {
                showNotification('Επιτυχής εισαγωγή δεδομένων', 'success');
                fileUploadModal.hide();
            } else {
                showNotification('Σφάλμα', 'error');
            }
        })
        .catch((error) => {
            console.error('Σφάλμα:', error);
        });
}

function toggleJsonExample() {
    const jsonExample = document.getElementById('jsonExample');
    const toggleButton = document.getElementById('toggleJsonButton');

    if (jsonExample.classList.contains('d-none')) {
        jsonExample.classList.remove('d-none');
        toggleButton.textContent = 'Απόκρυψη Μορφής Αρχείου JSON';
    } else {
        jsonExample.classList.add('d-none');
        toggleButton.textContent = 'Εμφάνιση Μορφής Αρχείου JSON';
    }
}
