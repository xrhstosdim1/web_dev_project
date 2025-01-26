<?php
include "../log-in-system/user_auth.php";
?>
<!DOCTYPE html>
<html lang="el" data-bs-theme="" id="htmlPage">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>ΣΥΔΕ - Professor</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="icon" type="image/x-icon" href="../images/logo_symbol.png">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
		<link rel="stylesheet" href="professor.css">
		<link rel="stylesheet" href="../globally_accessed/notifications/notifications.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
		<link href="../globally_accessed/dark-mode/switch-style.css" rel="stylesheet">
	</head>
	<body>
		<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarTogglerDemo01">
					<img src="../images/logo_vertical_cut.png" alt="" width="7%" height=auto; class="navbar-brand" href="#">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="thesesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Θέματα </a>
							<ul class="dropdown-menu" aria-labelledby="thesesDropdown">
								<li>
									<a class="dropdown-item" href="#" data-section="assign">Δημιουργία Θεμάτων</a>
								</li>
								<li>
									<a class="dropdown-item" href="#" data-section="edit-topics">Επεξεργασία Θεμάτων</a>
								</li>
							</ul>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="my-theses">Οι διπλωματικές μου</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="invitations">Προσκλήσεις</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" data-section="statistics">Στατιστικά</a>
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
			<!-- // *** Δημιουργία Θεμάτων *** \\ -->
			<div class="section-container" id="assign" style="display: none;">
				<h2 class="mb-4 text-primary text-center">Ανακοίνωση θεμάτων</h2>
				<form id="publish-form" method="POST" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="title" class="form-label">Τίτλος Θέματος *</label>
						<input type="text" id="title" name="topic" class="form-control" placeholder="Εισάγετε τον τίτλο του θέματος" required>
					</div>
					<div class="mb-3">
						<label for="summary" class="form-label">Σύνοψη *</label>
						<textarea id="summary" name="summary" class="form-control" rows="4" placeholder="Εισάγετε μια σύντομη περιγραφή του θέματος" required></textarea>
					</div>
					<div class="mb-3">
						<label for="upload-file" class="form-label">Ανέβασμα PDF</label>
						<input type="file" id="upload-file" name="upload-file" class="form-control" accept=".pdf">
					</div>
					<div class="mb-3 position-relative">
						<label for="student-search" class="form-label">Αριθμός Μητρώου Φοιτητή (προαιρετικό)</label>
						<input type="text" id="student-search" class="form-control" placeholder="Αναζήτηση φοιτητή με όνομα ή ΑΜ">
						<ul id="student-suggestions" class="list-group mt-2" style="display: none; position: absolute; z-index: 1000; width: 100%;"></ul>
						<input type="hidden" id="student-am" name="student-am">
						<!-- hidden input gia to am tou foithth -->
					</div>
					<button type="submit" class="btn btn-primary w-100">Καταχώρηση Θέματος</button>
				</form>
			</div>
			<!-- // *** Επεξεργασία Θεμάτων *** \\ -->
			<div id="edit-topics" class="section-container" style="display: none;">
				<div class="card-body p-4">
					<h2 class="mb-4 text-primary text-center">Επεξεργασία Θεμάτων</h2>
					<div class="table-responsive">
						<table class="table table-hover text-center align-middle">
							<thead class="table">
								<tr>
									<th>ID</th>
									<th>Θέμα</th>
									<th>Περιγραφή</th>
									<th>Κατάσταση</th>
									<th></th>
								</tr>
							</thead>
							<tbody id="topics-table-body">
								<!-- dynamic -->
							</tbody>
						</table>
					</div>
					<!-- Pagination -->
					<nav aria-label="Pagination" class="mt-3">
						<ul class="pagination justify-content-center" id="pagination-edit-container">
							<!-- pagination -->
						</ul>
					</nav>
				</div>
			</div>
			<!-- edit modal -->
			<div class="modal fade" id="editThesisModal" tabindex="-1" aria-labelledby="editThesisModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content shadow">
						<div class="modal-header bg-primary text-white">
							<h5 class="modal-title fw-bold" id="editThesisModalLabel">
								<i class="bi bi-pencil-fill me-2"></i>Επεξεργασία Διπλωματικής: <span id="edit-thesis-topic-header" class="fw-light"></span>
							</h5>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form id="edit-thesis-form">
								<input type="hidden" id="edit-thesis-id">
								<div class="mb-4">
									<h6 class="fw-bold d-flex align-items-center mb-2">
										<i class="bi bi-card-heading me-2 text-primary"></i> Τίτλος Θέματος
									</h6>
									<input type="text" class="form-control" id="edit-thesis-topic" placeholder="Προσθέστε τον τίτλο">
								</div>
								<div class="mb-4">
									<h6 class="fw-bold d-flex align-items-center mb-2">
										<i class="bi bi-card-text me-2 text-primary"></i> Περιγραφή
									</h6>
									<textarea class="form-control" id="edit-thesis-summary" rows="4" placeholder="Προσθέστε περιγραφή"></textarea>
								</div>
								<div class="mb-4">
									<h6 class="fw-bold d-flex align-items-center mb-2">
										<i class="bi bi-file-earmark-arrow-up-fill me-2 text-primary"></i> Ανέβασμα Αρχείου
									</h6>
									<input type="file" class="form-control" id="edit-thesis-file">
									<small id="current-file-name" class="form-text text-muted">Τρέχον αρχείο: Δεν έχει οριστεί</small>
								</div>
								<div class="mb-4 position-relative">
									<h6 class="fw-bold d-flex align-items-center mb-2">
										<i class="bi bi-person-fill me-2 text-primary"></i> Ανάθεση σε φοιτητή
									</h6>
									<input type="text" id="edit-student-search" class="form-control" placeholder="Αναζήτηση φοιτητή με όνομα ή ΑΜ">
									<ul id="edit-student-suggestions" class="list-group mt-2" style="display: none; position: absolute; z-index: 1000; width: 100%;"></ul>
									<input type="hidden" id="edit-student-am" name="student-am">
									<!-- hidden input gia to am tou foithth -->
								</div>
							</form>
						</div>
						<div class="modal-footer ">
							<div class="d-flex w-100">
								<button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal"> Άκυρο </button>
								<button type="button" class="btn btn-primary" onclick="saveThesisEdits()">Αποθήκευση</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- // ***  Οι Διπλωματικές Μου *** \\ -->
			<div id="my-theses" class="section-container" style="display: none;">
				<h2 class="mb-4 text-primary text-center">Οι διπλωματικές μου</h2>
				<!-- filtra -->
				<div class="d-flex justify-content-between align-items-center mb-4  p-3 rounded">
					<div class="d-flex flex-wrap align-items-center gap-3" id="filters-container">
						<!-- status filtering -->
						<div>
							<label for="my-filter-status" class="form-label mb-1">Κατάσταση:</label>
							<select id="my-filter-status" class="form-select">
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
						<!-- rolos filtering -->
						<div>
							<label for="filter-role" class="form-label mb-1">Ρόλος:</label>
							<select id="filter-role" class="form-select">
								<option value="all">Όλοι</option>
								<option value="supervisor">Επιβλέπων</option>
								<option value="member">Μέλος Τριμελούς</option>
							</select>
						</div>
					</div>
					<!-- export -->
					<div class="btn-group">
						<button class="btn btn-primary" onclick="handleExport()">Εξαγωγή όλων</button>
						<button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
							<span class="visually-hidden">Επιλογή μορφής</span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a class="dropdown-item" href="#" onclick="handleExportFormatChange('CSV')">CSV</a>
							</li>
							<li>
								<a class="dropdown-item" href="#" onclick="handleExportFormatChange('JSON')">JSON</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="card shadow-sm">
					<div class="card-body p-4">
						<div class="table-responsive">
							<table class="table table-hover text-center align-middle">
								<thead class="table">
									<tr>
										<th>ID</th>
										<th>Θέμα</th>
										<th>Φοιτητής</th>
										<th>Κατάσταση</th>
										<th>Ρόλος</th>
										<th></th>
									</tr>
								</thead>
								<tbody id="my-theses-table-body">
									<!-- dynamic -->
								</tbody>
							</table>
						</div>
						<!-- Pagination -->
						<nav aria-label="Pagination" class="mt-3">
							<ul class="pagination justify-content-center" id="pagination-container">
								<!-- pagination -->
							</ul>
						</nav>
					</div>
				</div>
			</div>
			<!-- view modal -->
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
								<i class="bi bi-patch-check-fill me-2 text-success"></i> Κατάσταση - Ενέργεις
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
									<small id="thesis-grading-status">N/A</small>
								</div>
								<div class="step">
									<span class="label">Ολοκληρώθηκε</span>
									<small>
										<span id="thesis-completion-date">N/A</span>
									</small>
								</div>
							</div>
							<!-- info box -->
							<div class="col-12  shadow-sm p-3">
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
								<span id="change-student-container" class="ms-auto"></span>
							</p>
							<p class="mb-1">
								<strong>Επιβλέπων:</strong>
								<span id="thesis-supervisor"></span>
							</p>
							<p class="mb-1">
								<strong>2ο μέλος επιτροπής:</strong>
								<span id="member2"></span>
							</p>
							<p class="mb-1">
								<strong>3ο μέλος επιτροπής:</strong>
								<span id="member3"></span>
							</p>
							<p class="mb-1">
								<strong>Συνημμένο Αρχείο:</strong>
								<a href="#" id="thesis-file-link" target="_blank" class="text-decoration-none text-primary">
									<span id="thesis-file-name"></span>
								</a>
							</p>
						</div>

						<div id="exetasi-content" class="sector mb-4" style="display: none;">
							<h6 class="fw-bold d-flex align-items-center mb-3">
								<i class="bi bi-list-check me-2 text-warning"></i> Λεπτομέρειες για την Εξέταση <button id="announce-exam-main-btn" class="btn btn-warning btn-sm ms-auto">Ανακοίνωση Εξέτασης</button>
							</h6>
							<div class="table-responsive">
								<table class="table align-middle">
									<tbody>
										<tr>
											<th class="text-nowrap">Πρόχειρο Κείμενο:</th>
											<td id="draft-file" class="text-muted">N/A</td>
										</tr>
										<tr>
											<th class="text-nowrap">Λίστα Συνδέσμων:</th>
											<td>
												<ul id="thesis-links" class="mb-0">
													<li class="text-muted">Δεν υπάρχουν σύνδεσμοι.</li>
												</ul>
											</td>
										</tr>
										<tr>
											<th class="text-nowrap">Ημερομηνία Εξέτασης:</th>
											<td id="exam-date" class="text-muted">N/A</td>
										</tr>
										<tr>
											<th class="text-nowrap">Αίθουσα ή σύνδεσμος:</th>
											<td id="exam-room" class="text-muted">N/A</td>
										</tr>
										<tr>
											<th class="text-nowrap">Κείμενο ανακοίνωσης:</th>
											<td id="ann_body_" class="text-muted">N/A</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<!-- modal anakoinwshs-->
						<div class="modal fade" id="examAnnouncementModal" tabindex="-1" aria-labelledby="examAnnouncementModalLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered modal-sm">
								<div class="modal-content">
									<div class="modal-header bg-warning text-white">
										<h5 class="modal-title" id="examAnnouncementModalLabel">Ανακοίνωση Εξέτασης</h5>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<form id="exam-announcement-form">
											<div class="mb-2">
												<label for="announcement" class="form-label">Ανακοίνωση</label>
												<textarea class="form-control form-control-sm" id="announcement" rows="3" required></textarea>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Κλείσιμο</button>
												<button type="submit" class="btn btn-warning btn-sm" id="announce-exam-submit-btn" disabled>Ανακοίνωση Εξέτασης</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>

						<!-- Vathmologio -->
						<div id="vathmologio" class="sector mb-4" style="display: none;" data-logged-in-email="<?php echo $_SESSION["email"]; ?>">
							<h6 class="fw-bold d-flex align-items-center mb-3">
								<i class="bi bi-pencil-square me-2 text-primary"></i> Βαθμολογία
							</h6>
							<div class="table-responsive shadow-sm rounded">
								<table class="table table-hover align-middle border text-center">
									<thead class="table-primary">
										<tr>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Βαθμολογητής</th>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Ποιότητα <br>(60%) </th>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Χρονικό Διάστημα <br>(15%) </th>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Ποιότητα & <br>Πληρότητα Κειμένου <br>(15%) </th>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Συνολική Εικόνα <br>Παρουσίασης <br>(10%) </th>
											<th class="text-center" style="font-size: 14px; padding: 10px;">Συνολική Βαθμολογία</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="fw-bold" id="vathmologio-epivlepon-onoma">Επιβλέπων</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="epivlepon-crit1" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="epivlepon-crit2" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="epivlepon-crit3" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="epivlepon-crit4" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td class="fw-bold text-muted" id="epivlepon-total">N/A</td>
										</tr>
										<tr>
											<td class="fw-bold" id="vathmologio-member2-onoma">Σύμβουλος 1</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member2-crit1" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member2-crit2" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member2-crit3" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member2-crit4" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td class="fw-bold text-muted" id="member2-total">N/A</td>
										</tr>
										<tr>
											<td class="fw-bold" id="vathmologio-member3-onoma">Σύμβουλος 2</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member3-crit1" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member3-crit2" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm " id="member3-crit3" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm" id="member3-crit4" min="0" max="10" step="0.5" onblur="validateRange(this);" disabled>
											</td>
											<td class="fw-bold text-muted" id="member3-total">N/A</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="text-end mt-3">
								<button class="btn btn-success btn-sm px-4" id="save-grades-btn">
									<i class="bi bi-save me-1"></i> Αποθήκευση Βαθμολογίας </button>
							</div>
						</div>
						<!-- Requests -->
						<div class="sector mb-4">
							<h6 class="fw-bold d-flex align-items-center mb-3">
								<i class="bi bi-envelope-paper-fill me-2 text-info"></i> Αιτήματα προς Επιβλέποντες
							</h6>
							<div id="thesis-actions" class="mt-2">
								<!-- dynamic -->
							</div>
						</div>
						<!-- Comments -->
						<div class="sector">
							<h6 class="fw-bold d-flex align-items-center mb-3">
								<i class="bi bi-chat-dots-fill me-2 text-secondary"></i> Σχόλια <button id="add-comment-btn" class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="collapse" data-bs-target="#add-comment-form" aria-expanded="false" aria-controls="add-comment-form">
									<i class="bi bi-plus-lg"></i> Προσθήκη Σχολίου </button>
							</h6>
							<div id="thesis-comments" class="mt-2">
								<!-- dynamic -->
							</div>
							<!-- Comment from-->
							<div class="collapse mt-3" id="add-comment-form">
								<form id="comment-form">
									<input type="hidden" name="id_diplomatikis" id="comment-diplomatikis-id">
									<input type="hidden" name="prof_email" id="comment-prof-email">
									<div class="mb-3">
										<textarea name="comment" id="comment-text" class="form-control" rows="3" placeholder="Γράψτε το σχόλιό σας εδώ..." maxlength="300"></textarea>
									</div>
									<button type="submit" class="btn btn-primary btn-sm">Αποθήκευση</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- // *** Προσκλήσεις *** \\ -->
			<div id="invitations" class="section-container" style="display: none;">
				<h2 class="mb-4 text-primary text-center">Προσκλήσεις Τριμελούς</h2>
				<div class="card shadow-sm">
					<div class="card-body p-4">
						<div class="table-responsive">
							<table class="table table-bordered table-hover text-center">
								<thead class="table">
									<tr>
										<th>Φοιτητής</th>
										<th>Επιβλέπων Καθηγητής</th>
										<th>Θέμα Διπλωματικής</th>
										<th>Κατάσταση</th>
										<th>Ενέργειες</th>
									</tr>
								</thead>
								<tbody id="invitations-table-body">
									<!-- dynamic -->
								</tbody>
							</table>
						</div>
						<!-- Pagination -->
						<nav aria-label="Pagination" class="mt-3">
							<ul class="pagination justify-content-center" id="pagination-inv-container">
								<!-- pagination -->
							</ul>
						</nav>
					</div>
				</div>
			</div>
			<!-- // *** Στατιστικά *** \\ -->
			<div id="statistics" class="section-container">
				<h2 class="mb-4 text-primary text-center">Στατιστικά</h2>
				<div class="container">
					<!-- Row 1 -->
					<div class="row g-4">
						<div class="col-md-6">
							<div class="stat-section">
								<h3>Ετήσια Κατανομή Διπλωματικών</h3>
								<div class="chart-container">
									<canvas id="yearlyChart"></canvas>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="stat-section">
								<h3>Αιτήματα: Αποδεκτά vs Απορριφθέντα</h3>
								<div class="chart-container">
									<canvas id="requestsChart"></canvas>
								</div>
							</div>
						</div>
					</div>
					<!-- Row 2 -->
					<div class="row g-4 mt-4">
						<div class="col-md-6">
							<div class="stat-section">
								<h3>Χρόνοι Αντίδρασης Αιτημάτων</h3>
								<div class="chart-container">
									<canvas id="responseTimeChart"></canvas>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="stat-section">
								<h3>Ποσοστά Βαθμολογίας (Άνω του 9)</h3>
								<div class="chart-container">
									<canvas id="gradesChart"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		
		<div class="notification-container" id="notification-container">
		</div> <?php include "../globally_accessed/footer.html"; ?>

		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script src="../globally_accessed/dark-mode/switch_script.js"></script>
		<script src="professor.js" defer></script>
		<script src="../globally_accessed/show_name_on_navbar.js" defer></script>
		<script src="../globally_accessed/notifications/notifications.js" defer></script>
		
	</body>
</html>