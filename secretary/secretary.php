<?php
include "../log-in-system/user_auth.php";// Check if user is logged in and if the role matches
?>
	<!DOCTYPE html>
<html lang="el" data-bs-theme="" id="htmlPage">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>ΣΥΔΕ - Secretary</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="icon" type="image/x-icon" href="../images/logo_symbol.png">
		<link rel="stylesheet" href="secretary.css">
		<link rel="stylesheet" href="../globally_accessed/notifications/notifications.css">
		<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
		<link href="../globally_accessed/dark-mode/switch-style.css" rel="stylesheet">
	</head>
	<body>
		<!-- Navbar -->
		<nav class="navbar navbar-expand-lg custom-navbar">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarTogglerDemo01">
					<img src="../images/logo_vertical_cut.png" alt="" width="7%" height=auto; class="navbar-brand" href="#">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="requests">Αιτήσεις</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="view-theses">Προβολή διπλωματικών</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="grading">Βαθμολόγιο</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="insert-data">Εισαγωγή δεδομένων</a>
						</li>
					</ul>
					<div class="d-flex align-items-center">
						<span id="user-name" class="me-3"></span>
						<div class="switch d-flex align-items-center">
                    		<input type="checkbox" class="checkbox" id="checkbox">
                    		<label for="checkbox" class="checkbox-label">
                        	<i class="fas fa-moon"></i>
                    		<i class="fas fa-sun"></i>
                    		<span class="ball"></span>
                    		</label>
               		 	</div>
						<button class="btn btn-outline-dark d-flex align-items-center" onclick="window.location.href='../log-in-system/logout.php'">
							<i class="fas fa-sign-out-alt me-2"></i> Αποσύνδεση </button>
					</div>
				</div>
			</div>
		</nav>


		<div class="container mt-5">
			<!-- // *** Section: Αιτήσεις -->
			<div id="requests" class="section-container" style="display: none;">
				<h2 class="mb-4 text-primary text-center">Αιτήσεις</h2>
				<div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded">
					<div class="d-flex flex-wrap align-items-center gap-3" id="requests-filters-container">
						<!-- Status Filtering -->
						<div>
							<label for="my-filter-status" class="form-label mb-1">Κατάσταση:</label>
							<select id="my-filter-status" class="form-select">
								<option value="pending">Εκκρεμείς</option>
								<option value="all">Όλες</option>
								<option value="accepted">Αποδεκτές</option>
								<option value="denied">Απορριφθείσες</option>
							</select>
						</div>
						<!-- Reason Filtering -->
						<div>
							<label for="filter-reason" class="form-label mb-1">Είδος:</label>
							<select id="filter-reason" class="form-select">
								<option value="all">Όλα</option>
								<option value="enarksi">Έναρξη εκπόνησης</option>
								<option value="complete">Ολοκλήρωση</option>
								<option value="cancel">Ακύρωση</option>
							</select>
						</div>
					</div>
				</div>
				<div class="card shadow-sm">
					<div class="card-body p-4">
						<div class="table-responsive">
							<table class="table table-hover text-center align-middle" id="requests-table">
								<thead class="table">
									<tr>
										<th>ID Αίτησης</th>
										<th>Αιτών</th>
										<th>Είδος Αίτησης</th>
										<th>ID Διπλωματικής</th>
										<th>Ημερομηνία Αίτησης</th>
										<th>Κατάσταση Αίτησης</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<!-- Dynamic rows will be added here -->
								</tbody>
							</table>
						</div>
						<!-- Pagination -->
                        <nav aria-label="Pagination" class="mt-3">
                            <ul class="pagination justify-content-center" id="pagination-container"></ul>
                        </nav>
					</div>
				</div>
			</div>
				<!-- // *** MODAL AITHSHS -->
				<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<div class="d-flex flex-column">
									<h5 class="modal-title fw-bold" id="requestModalLabel">Αίτηση Έναρξης Εκπόνησης</h5>
									<small class="text" id="requestDate">Ημερομηνία Αίτησης: </small>
								</div>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Κλείσιμο"></button>
							</div>
							<div class="modal-body">
								<div class="container">
									<!-- Πληροφορίες Θέματος -->
									<div class="mb-4 p-3 border rounded">
										<h6 class="fw-bold text-primary mb-3">Πληροφορίες Θέματος</h6>
										<div class="mb-2">
											<label for="thesesCreationDate" class="form-label fw-bold text-secondary">Ημερομηνία Δημιουργίας Θέματος:</label>
											<div id="thesesCreationDate"></div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Θέμα Διπλωματικής:</label>
											<div id="diplomaTopic"></div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Περιγραφή:</label>
											<div id="description"></div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Συννημένο Αρχείο:</label>
											<div>
												<a href="#" target="_blank" id="attachmentLink" class="text-primary">Δεν υπάρχει αρχείο</a>
											</div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Επιβλέπων:</label>
											<div id="supervisor"></div>
										</div>
									</div>
									<!-- Πληροφορίες Τριμελούς Επιτροπής -->
									<div class="p-3 border rounded">
										<h6 class="fw-bold text-primary mb-3">Πληροφορίες Τριμελούς Επιτροπής</h6>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Μέλος Τριμελούς 2:</label>
											<div id="member2"></div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Μέλος Τριμελούς 3:</label>
											<div id="member3"></div>
										</div>
										<div class="mb-2">
											<label class="form-label fw-bold text-secondary">Φοιτητής:</label>
											<div id="student"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-danger me-auto" id="rejectButton">Απόρριψη</button>
								<button type="button" class="btn btn-success" id="approveButton">Έγκριση</button>
							</div>
						</div>
					</div>
				</div>
				<!-- // *** MODAL EISAGWGHS AP KAI SXOLIOU -->
				<div class="modal fade" id="protocolModal" tabindex="-1" aria-labelledby="protocolModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-md modal-dialog-centered">
						<div class="modal-content shadow-lg">
							<div class="modal-header">
								<h5 class="modal-title fw-bold text" id="protocolModalLabel">Έγκριση Έναρξης Εκπόνησης</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Κλείσιμο"></button>
							</div>
							<div class="modal-body">
								<div class="mb-3">
									<label for="protocolNumber" class="form-label fw-bold">Αριθμός Πρωτοκόλλου</label>
									<input type="number" class="form-control text-center" id="protocolNumber" placeholder="Εισάγετε αριθμό πρωτοκόλλου" step="1" required>
								</div>
								<div class="mb-3">
									<label for="comment" class="form-label fw-bold">Σχόλιο</label>
									<textarea class="form-control" id="comment" rows="3" maxlength="150" placeholder="Σχόλιο απόφασης (μέγιστο 150 χαρακτήρες)"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Ακύρωση</button>
								<button type="button" class="btn btn-primary btn-sm" id="saveProtocolButton">Αποθήκευση</button>
							</div>
						</div>
					</div>
				</div>


			<!-- // *** Section: Oles oi diplwmatikes -->
			<div id="view-theses" class="section-container" style="display: none;">
				<h2 class="mb-4 text-primary text-center">Όλες οι διπλωματικές</h2>
				<div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded">
					<div class="d-flex flex-wrap align-items-center gap-3" id="thesis-filters-container">
						<!-- Status Filtering -->
						<div>
							<label for="oles-diplwmatikes-filter-status" class="form-label mb-1">Κατάσταση:</label>
							<select id="oles-diplwmatikes-filter-status" class="form-select">
								<option value="all">Όλες</option>
								<option value="diathesimi">Διαθέσιμες</option>
								<option value="pros_anathesi">Επιλεγμένες από φοιτητή</option>
								<option value="pros_egrisi">Προς Έγκριση</option>
								<option value="energi">Ενεργές</option>
								<option value="oloklirwmeni">Ολοκληρωμένες</option>
								<option value="exetasi">Υπό Εξέταση</option>
								<option value="akurwmeni">Ακυρωμένες</option>
							</select>
						</div>
					</div>
				</div>
				<div class="card shadow-sm">
					<div class="card-body p-4">
						<div class="table-responsive">
							<table class="table table-hover text-center align-middle">
								<thead class="table">
									<tr>
										<th>ID Διπλωματικής</th>
										<th>Θέμα</th>
										<th>Φοιτητής</th>
										<th>Κατάσταση</th>
										<th></th>
									</tr>
								</thead>
								<tbody id="my-theses-table-body"></tbody>
							</table>
						</div>
						<!-- Pagination -->
						<nav aria-label="Pagination" class="mt-3">
							<ul class="pagination justify-content-center" id="pagination-container-diplomatikes"></ul>
						</nav>
					</div>
				</div>
			</div>
				<!-- // *** MODAL DIPLOMATIKHS -->
				<div class="modal fade" id="thesisModal" tabindex="-1" aria-labelledby="thesisModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content shadow">
							<div class="modal-header bg-primary text-white">
								<h5 class="modal-title fw-bold" id="thesisModalLabel">
									<i class="bi bi-info-circle-fill me-2"></i>Λεπτομέρειες Διπλωματικής: <span id="thesis-topic-header" class="fw-light"></span>
								</h5>
								<div id="cancel-assignment-container" class="ms-auto"></div>
								<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="container my-5">
								<h6 class="fw-bold d-flex align-items-center mb-3">
									<i class="bi bi-patch-check-fill me-2 text-success"></i> Κατάσταση - Ενέργειες
								</h6>
								<div class="progress-timeline">
									<div class="step completed" id='diathesimi-step'>
										<span class="label">Διαθέσιμη</span>
										<small>
											<span id="thesis-creation-date"></span>
										</small>
									</div>
									<div class="step">
										<span class="label">Επιλέχθηκε</span>
										<small>
											<span id="thesis-selection-date">N/A</span>
										</small>
									</div>
									<div class="step">
										<span class="label">Προς έγκριση</span>
										<small>
											<span id="thesis-requested-date">N/A</span>
										</small>
									</div>
									<div class="step">
										<span class="label">Ενεργή</span>
										<small>
											<span id="thesis-start-date">N/A</span>
										</small>
									</div>
									<div class="step" id="exam-step">
										<span class="label">Εξέταση</span>
										<small>
											<span id="thesis-exam-date">N/A</span>
										</small>
									</div>
									<div class="step" id="grading-step">
										<span class="label">Βαθμολόγηση</span>
										<small id="thesis-grading-date">N/A</small>
									</div>
									<div class="step">
										<span class="label">Ολοκληρώθηκε</span>
										<small>
											<span id="thesis-completion-date">N/A</span>
										</small>
									</div>
								</div>
								<!-- info box -->
								<div class="col-12 shadow-sm p-3">
									<div class="d-flex align-items-center">
										<i class="bi bi-info-circle text-primary me-2"></i>
										<small>
											<span class="text-muted small" id="status-info">-</span>
										</small>
									</div>
								</div>
							</div>
							
							<div id="thesis-details" class="sector mb-4">
								<h6 class="fw-bold d-flex align-items-center mb-3">
									<i class="bi bi-card-text me-2 text-primary"></i> Γενικές Πληροφορίες
								</h6>
								<p class="mb-1">
									<strong>Περίληψη:</strong>
									<span id="thesis-summary"></span>
								</p>
								<p class="mb-1">
									<strong>Φοιτητής:</strong>
									<span id="thesis-student"></span>
									<small>
										<span id="deny-student"></span>
									</small>
								</p>
								<p class="mb-1">
									<strong>Επιβλέπων:</strong>
									<span id="thesis-supervisor"></span>
								</p>
								<p class="mb-1">
									<strong>2ο μέλος επιτροπής:</strong>
									<span id="member2_"></span>
								</p>
								<p class="mb-1">
									<strong>3ο μέλος επιτροπής:</strong>
									<span id="member3_"></span>
								</p>
								<p class="mb-1">
									<strong>Συνημμένο Αρχείο:</strong>
									<span id="thesis-file-name"></span>
								</p>
								<p class="mb-1">
									<strong>Χρόνος από την ανάθεση:</strong>
									<span id="time-since-assignment">-</span>
								</p>
							</div>

							<div id="exam-details" class="sector mb-4">
								<h6 class="fw-bold d-flex align-items-center mb-3">
									<i class="bi bi-calendar-event me-2 text-primary"></i> Πληροφορίες Εξέτασης
								</h6>
								<p class="mb-1">
									<strong>Ημερομηνία Εξέτασης:</strong>
									<span id="_exam-date">Δεν έχει οριστεί</span>
								</p>
								<p class="mb-1">
									<strong>Τοποθεσία:</strong>
									<span id="_exam-location">Δεν έχει οριστεί</span>
								</p>
								<p class="mb-1">
									<strong>Σύνδεσμος Νημερτή:</strong>
									<span id="_nemertes_link">Δεν έχει οριστεί</span>
								</p>
								<p class="mb-1">
									<strong>Συνημμένο Αρχείο Φοιτητή:</strong>
									<span id="student-thesis-file-name">N/A</span>
								</p>
							</div>
						</div>
					</div>
				</div>



			<!-- // *** Section: Vathmologio -->
			<div class="section-container" id="grading" style="display: none;">
				<div class="container py-5">
					<h2 class="text-center fw-bold text-primary mb-5">Βαθμολόγιο</h2>
					<p class="text-center text-muted mb-4">Εδώ εμφανίζονται οι καταχωρημένοι βαθμοί για μια διπλωματική, ανεξάρτητα από το αν έχει εγκριθεί ή όχι. Η έγκριση / απόρριψη γίνεται στις αιτήσεις.</p>
					
					<!-- Search and Filter Section -->
					<div class="row justify-content-center mb-5">
						<div class="col-md-8">
							<!-- Αναζήτηση με ID Διπλωματικής και ΑΜ Φοιτητή -->
							<div class="input-group mb-3">
								<input type="number" id="thesis-id" class="form-control" placeholder="Εισάγετε το ID της Διπλωματικής Εργασίας" />
								<input type="text" id="student-search" class="form-control" placeholder="Εισάγετε το όνομα του φοιτητή" />
								<input type="hidden" id="student-id" />
								<button id="search-thesis-btn" class="btn btn-primary">Αναζήτηση</button>
								<button id="view-all-btn" class="btn btn-outline-secondary">Προβολή Όλων</button>
							</div>
							
							<!-- Λίστα προτάσεων φοιτητών -->
							<ul id="suggestions-list" class="list-group mt-2" style="display: none; position: absolute; z-index: 1000; width: 100%;"></ul>
						</div>
					</div>

					
					<!-- Thesis Table -->
					<div class="card shadow-sm">
						<div class="card-body p-4">
							<div class="table-responsive">
								<table class="table table-hover text-center align-middle">
									<thead class="table">
										<tr>
											<th>Φοιτητής</th>
											<th>ID Διπλωματικής</th>
											<th>Επιβλέπων Καθηγητής</th>
											<th>Μέλος 1</th>
											<th>Μέλος 2</th>
											<th>Ημερομηνία Εξέτασης</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<!-- Dynamic rows will be added here -->
									</tbody>
								</table>
							</div>
							<!-- Pagination -->
							<nav aria-label="Pagination" class="mt-3">
								<ul class="pagination justify-content-center"></ul>
							</nav>
						</div>
					</div>
				</div>
			</div>
				<!-- // *** MODAL VATHMOLOGIOU -->
				<div class="modal fade" id="gradingModal" tabindex="-1" aria-labelledby="gradingModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content shadow">
							<div class="modal-header bg-primary text-white">
								<h5 class="modal-title fw-bold" id="gradingModalLabel">
									<i class="bi bi-info-circle-fill me-2"></i>Αναλυτική Βαθμολογία: <span id="vathm-thesis-topic-header" class="fw-light"></span>
								</h5>
								<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="container my-5">
									<!-- Status Section -->
									<h6 class="fw-bold d-flex align-items-center mb-3">
										<i class="bi bi-patch-check-fill me-2 text-success"></i> Κατάσταση - Ενέργειες
									</h6>
									<div class="vathm-progress-timeline">
										<div class="progress-timeline">
											<div class="step" id="exam-step">
												<span class="label">Εξέταση</span>
												<small id="vathm-thesis-exam-date">N/A</small>
											</div>
											<div class="step" id="grading-step">
												<span class="label">Βαθμολόγηση</span>
												<small id="vathm-thesis-grading-date">N/A</small>
											</div>
											<div class="step" id="completed-step">
												<span class="label">Ολοκληρώθηκε</span>
												<small>
													<span id="vathm-thesis-completion-date">N/A</span>
												</small>
											</div>
										</div>
									</div>

									<!-- Status info -->
									<div class="col-12 shadow-sm p-3">
										<div class="d-flex align-items-center">
											<i class="bi bi-info-circle text-primary me-2"></i>
											<small>
												<span class="text-muted small" id="vathm-status-info">-</span>
											</small>
										</div>
									</div>
								</div>
								<div id="vathm-thesis-details" class="sector mb-4">
									<h6 class="fw-bold d-flex align-items-center mb-3">
										<i class="bi bi-card-text me-2 text-primary"></i> Λεπτομέρειες Διπλωματικής
									</h6>
									<p class="mb-1">
										<strong>Περίληψη:</strong>
										<span id="vathm-thesis-summary">N/A</span>
									</p>
									<p class="mb-1">
										<strong>Φοιτητής:</strong>
										<span id="vathm-thesis-student">N/A</span>
									</p>
									<p class="mb-1">
										<strong>Επιβλέπων:</strong>
										<span id="vathm-thesis-supervisor">N/A</span>
									</p>
									<p class="mb-1">
										<strong>2ο μέλος επιτροπής:</strong>
										<span id="vathm-member2_">N/A</span>
									</p>
									<p class="mb-1">
										<strong>3ο μέλος επιτροπής:</strong>
										<span id="vathm-member3_">N/A</span>
									</p>
									<p class="mb-1">
										<strong>Συνημμένο Αρχείο:</strong>
										<a href="#" id="vathm-thesis-file-link" target="_blank" class="text-decoration-none text-primary">
											<span id="vathm-thesis-file-name">N/A</span>
										</a>
									</p>
									<p class="mb-1">
										<strong>Σύνδεσμος αποθετηρίου:</strong>
										<span id="nhmerths">N/A</span>
									</p>
								</div>
								<h5 class="mt-4 mb-3">Βαθμολογία: <span class="mt-4 mb-3" id="final_grade">-</span></h5>
								<div class="table-responsive">
									<table class="table table-hover align-middle text-center">
										<thead class="table">
											<tr>
												<th>Βαθμολογητής</th>
												<th>Κριτήριο 1</th>
												<th>Κριτήριο 2</th>
												<th>Κριτήριο 3</th>
												<th>Κριτήριο 4</th>
												<th>Τελικός Βαθμός</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td id="prof1-name">--</td>
												<td id="prof1-grade-1">--</td>
												<td id="prof1-grade-2">--</td>
												<td id="prof1-grade-3">--</td>
												<td id="prof1-grade-4">--</td>
												<td id="prof1-final-grade">--</td>
											</tr>
											<tr>
												<td id="prof2-name">--</td>
												<td id="prof2-grade-1">--</td>
												<td id="prof2-grade-2">--</td>
												<td id="prof2-grade-3">--</td>
												<td id="prof2-grade-4">--</td>
												<td id="prof2-final-grade">--</td>
											</tr>
											<tr>
												<td id="prof3-name">--</td>
												<td id="prof3-grade-1">--</td>
												<td id="prof3-grade-2">--</td>
												<td id="prof3-grade-3">--</td>
												<td id="prof3-grade-4">--</td>
												<td id="prof3-final-grade">--</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Κλείσιμο</button>
							</div>
						</div>
					</div>
				</div>



			<!-- // *** Section: Insert data -->
			<div id="insert-data" class="section-container active">
				<div class="container">
					<div class="row g-4 justify-content-center align-items-center">
						<!-- Manual Insertion Card -->
						<div class="col-md-6 col-lg-5">
							<div class="card shadow-sm text-center p-4 border-0">
								<div class="card-body">
									<div class="icon-container bg-primary text-white mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
										<i class="fas fa-upload fa-2x"></i>
									</div>
									<h5 class="card-title text-primary fw-bold mb-3">Ανέβασμα Αρχείου</h5>
									<p class="card-text text-muted mb-4">Επιλέξτε το αρχείο JSON που θέλετε να ανεβάσετε.</p>
									<input type="file" id="json-file" class="form-control mb-3" accept=".json" onchange="handleFileSelect(event)" hidden>
									<button class="btn btn-primary fw-bold px-5 py-2" onclick="openFileUploadModal()">Ανέβασμα αρχείου</button>

								</div>
							</div>
						</div>
						<!-- USIDAS Card -->
						<div class="col-md-6 col-lg-5">
							<div class="card shadow-sm text-center p-4 border-0">
								<div class="card-body">
									<div class="icon-container bg-success text-white mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
										<i class="fas fa-sync-alt fa-2x"></i>
									</div>
									<h5 class="card-title text-success fw-bold mb-3">Αυτόματη Ενημέρωση</h5>
									<p class="card-text text-muted mb-4">Ενημερώστε αυτόματα τα δεδομένα μέσω USIDAS.</p>
									<button class="btn btn-success fw-bold px-5 py-2" onclick="get_data_usidas()">Ενημέρωση</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
				<!-- // *** MMODAL ARXEIOU -->
				<div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="fileUploadModalLabel">Ανέβασμα Αρχείου JSON</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<!-- Επικεντρωνόμαστε στο ανέβασμα αρχείου -->
								<p class="mb-3">Επιλέξτε το αρχείο JSON που θέλετε να ανεβάσετε. Το αρχείο πρέπει να περιέχει φοιτητές και καθηγητές.</p>
								<p class="mb-3"><small>Όλοι οι κωδικοί αρχικοποιούνται σε "password"</small></p>
								<input type="file" id="json-file-modal" class="form-control mb-3" accept=".json" />
								<!-- Διακριτικό κουμπί για εμφάνιση του δείγματος -->
								<div class="text-end">
									<button id="toggleJsonButton" class="btn btn-sm btn-link text-primary" type="button" onclick="toggleJsonExample()">Προβολή μορφής αρχείου</button>
								</div>

								<!-- JSON Example (Initially Hidden) -->
								<div id="jsonExample" class=" border rounded p-3 d-none">
									<pre>

	{
		"students": [
			{
				"name": "John",
				"surname": "Doe",
				"student_number": 12345678,
				"email": "john.doe@example.com"
			}
		],
		"professors": [
			{
				"name": "Jane",
				"surname": "Smith",
				"email": "jane.smith@example.com",
				"topic": "Computer Science"
			}
		]
	}
									</pre>
								</div>
								<span id="upload-feedback" class="mt-3"></span>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
								<button type="button" class="btn btn-primary" id="upload-button-modal" onclick="handleModalFileUpload()">Ανέβασμα</button>
							</div>
						</div>
					</div>
				</div>








        </div>

		<div class="notification-container" id="notification-container">
        </div> <?php include "../globally_accessed/footer.html"; ?>

	<script src="../globally_accessed/dark-mode/switch_script.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="secretary.js" defer></script>
	<script src="../globally_accessed/show_name_on_navbar.js" defer></script>
	<script src="../globally_accessed/notifications/notifications.js" defer></script>
		
	</body>
</html>