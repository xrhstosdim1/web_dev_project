<?php
include('../log-in-system/user_auth.php'); // Check if user is logged in and if the role matches
?>

<!DOCTYPE html>
<html lang="el" data-bs-theme="" id="htmlPage">
<head>
	<meta charset='utf-8'>
	<meta http-equiv='X-UA-Compatible' content='IE=edge'>
	<title>ΣΥΔΕ - Student</title>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="icon" type="image/x-icon" href="../images/logo_symbol.png">
	<link rel='stylesheet' type='text/css' href='student.css'>
	<link rel="stylesheet" type="text/css" href="../globally_accessed/notifications/notifications.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="../globally_accessed/dark-mode/switch-style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg custom-navbar">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
      <img src="../images/logo_vertical_cut.png" alt="" width="7%" height=auto; class="navbar-brand" href="#">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="my-thesis">Η διπλωματική μου</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="available">Διαθέσιμα θέματα</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="thesis-info">Προβολή θέματος</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="profile">Προφίλ</a>
        </li>
      </ul>
      <div class="d-flex align-items-center">
              <div class="switch d-flex align-items-center">
                    <input type="checkbox" class="checkbox" id="checkbox">
                    <label for="checkbox" class="checkbox-label">
                        <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                    <span class="ball"></span>
                    </label>
                  </div>
                <span id="user-name" class="me-3"></span>
                <button class="btn btn-outline-dark d-flex align-items-center" onclick="window.location.href='../log-in-system/logout.php'">
                    <i class="fas fa-sign-out-alt me-2"></i> Αποσύνδεση
                </button>
      </div>
    </div>
  </div>
</nav>



<div class="container mt-3">


  <!--// *** Η ΔΙΠΛΩΜΑΤΙΚΗ ΜΟΥ *** \\ -->
    <div id="my-thesis" class="section active">
        <h2>Η Διπλωματική Μου</h2>
        <div id="my-thesis-content">
            <!-- Το περιεχόμενο θα φορτώνεται δυναμικά -->
        </div>
    </div>



  <!--// *** ΔΙΑΘΕΣΙΜΑ ΘΕΜΑΤΑ *** \\ -->
  <div id="available" class="section">
    <h2>Διαθέσιμα Θέματα</h2>
    <br>
    <br>
    <div class="table-responsive">
      <table class="table" id="theses-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Επιβλέπων καθηγητής</th>
            <th>Θέμα</th>
            <th>Περίληψη</th>
            <th>Ενέργειες</th>
          </tr>
        </thead>
        <tbody>
          <!-- Dynamic rows will be populated here -->
        </tbody>
      </table>
    </div>
    <nav>
      <ul id="pagination-container" class="pagination justify-content-center">
        <!-- Pagination buttons will be dynamically created here -->
      </ul>
    </nav>
  </div>

  <!--// *** modal *** \\ -->
  <div class="modal fade" id="thesisModal" tabindex="-1" aria-labelledby="thesisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow">
        <!-- Modal Header -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="thesisModalLabel">
            <i class="bi bi-info-circle-fill me-2"></i> Λεπτομέρειες Θέματος
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body px-4 py-3">
          <div class="row mb-3">
            <div class="col-3 text-secondary fw-bold">Τίτλος:</div>
            <div class="col-9 text fw-bold" id="thesis-title">N/A</div>
          </div>
          <div class="row mb-3">
            <div class="col-3 text-secondary fw-bold">Περιγραφή:</div>
            <div class="col-9 text" id="thesis-description">N/A</div>
          </div>
          <div class="row">
            <div class="col-3 text-secondary fw-bold">Συνημμένο Αρχείο:</div>
            <div class="col-9">
              <a href="#" id="thesis-file-link" target="_blank" class="text-primary text-decoration-none fw-bold">
                <span id="thesis-file-name">Δεν υπάρχει διαθέσιμο αρχείο</span>
              </a>
            </div>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Ακύρωση </button>
          <button type="button" class="btn btn-primary" id="select-thesis-button" data-id="">
            <i class="bi bi-check-circle me-2"></i> Επιλογή Θέματος </button>
        </div>
      </div>
    </div>
  </div>


  <!--// *** ΠΡΟΒΟΛΗ ΘΕΜΑΤΟΣ *** \\ -->
  <div id="thesis-info" class="section">
    <h2>Προβολή θέματος</h2>
    <!-- Progress Bar Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Στάδιο Κατάστασης</h5>
        <div class="progress" style="height: 30px;">
          <div id="status-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"> - </div>
        </div>
        <p class="mt-3">Χρόνος από την ανάθεση: <span id="time-since-assignment">-</span>
        </p>
      </div>
    </div>
    <!-- Thesis Details Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Λεπτομέρειες</h5>
        <p>
          <strong>Θέμα:</strong>
          <span id="thesis-topic">-</span>
        </p>
        <p>
          <strong>Περιγραφή:</strong>
          <span id="thesis-summary">-</span>
        </p>
        <p>
          <strong>Συνημμένο Αρχείο:</strong>
          <span id="my-thesis-file-link">Δεν υπάρχει διαθέσιμο αρχείο από τον καθηγητή.</span>
        </p>
      </div>
    </div>
    <!-- Committee Members Section -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Μέλη Τριμελούς Επιτροπής</h5>
        <table class="table table-bordered">
          <tbody>
            <tr>
              <th>Επιβλέπων:</th>
              <td id="supervisor-member">-</td>
            </tr>
            <tr>
              <th>Μέλος 1:</th>
              <td id="committee-member1">-</td>
            </tr>
            <tr>
              <th>Μέλος 2:</th>
              <td id="committee-member2">-</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- No Thesis Message -->
    <div id="no-thesis-message" class="alert alert-warning mt-4" style="display: none;">
      <strong>Δεν υπάρχει θέμα εργασίας.</strong>
    </div>
  </div>


  <!--// *** ΠΡΟΦΙΛ *** \\ -->
  <div id="profile" class="section">
    <h2>Το προφίλ μου</h2>
    <div class="container mt-4">
      <form id="profileForm">
        <div class="row mb-3">
          <label for="profile-first-name" class="col-sm-3 col-form-label">Όνομα:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-first-name" name="first_name" class="form-control" disabled>
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-last-name" class="col-sm-3 col-form-label">Επώνυμο:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-last-name" name="last_name" class="form-control" disabled>
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-email" class="col-sm-3 col-form-label">Email:</label>
          <div class="col-sm-9">
            <input type="email" id="profile-email" name="email" class="form-control" disabled>
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-am" class="col-sm-3 col-form-label">Αριθμός Μητρώου:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-am" name="am" class="form-control" disabled>
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-street" class="col-sm-3 col-form-label">Οδός:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-street" name="street" class="form-control">
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-str-number" class="col-sm-3 col-form-label">Αριθμός:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-str-number" name="str_number" class="form-control">
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-city" class="col-sm-3 col-form-label">Πόλη:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-city" name="city" class="form-control">
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-postcode" class="col-sm-3 col-form-label">Ταχυδρομικός Κώδικας:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-postcode" name="postcode" class="form-control" maxlength="5" pattern="\d{5}">
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-mobile" class="col-sm-3 col-form-label">Κινητό:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-mobile" name="mobile-phone" class="form-control" maxlength="10" pattern="^(\d{10})?$"> <!-- pattern gia 10 pshfia h null -->
          </div>
        </div>
        <div class="row mb-3">
          <label for="profile-landline" class="col-sm-3 col-form-label">Σταθερό:</label>
          <div class="col-sm-9">
            <input type="text" id="profile-landline" name="landline-phone" class="form-control" maxlength="10" pattern="^(\d{10})?$"> <!-- pattern gia 10 pshfia h null -->
          </div>
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-primary">Αποθήκευση</button>
        </div>
      </form>
    </div>
  </div>
</div>



<script src="../globally_accessed/dark-mode/switch_script.js"></script>
<script src="student.js" defer></script>
<script src="../globally_accessed/notifications/notifications.js" defer></script>
<script src="../globally_accessed/show_name_on_navbar.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>





<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<div class="notification-container" id="notification-container"></div>
<?php include '../globally_accessed/footer.html'; ?>
</body>
</html>