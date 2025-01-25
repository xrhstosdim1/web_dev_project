<!DOCTYPE html>
<html lang="el" data-bs-theme="" id="htmlPage">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ΣΥΔΕ</title>
    <link rel="icon" type="image/x-icon" href="../images/logo_symbol.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../globally_accessed/notifications/notifications.css">
    <link rel="stylesheet" href="guest.css">
    <link href="../globally_accessed/dark-mode/switch-style.css" rel="stylesheet">
</head>


<body>
    <nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/logo_vertical_cut.png" alt="Logo" width="20%" height="20%" class="d-inline-block align-text-center">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- an xreiastoun menou koumpua edw -->
                </ul>
                <div class="switch">
                <input type="checkbox" class="checkbox" id="checkbox">
                <label for="checkbox" class="checkbox-label">
                 <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <span class="ball"></span>
                </label>
                </div>
                <button class="btn btn-success d-flex align-items-center" onclick="window.location.href='../log-in-system/login.html'">
                    <i class="fas fa-sign-in-alt me-2"></i> Σύνδεση
                </button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4">
            <div class="col-md-6 mx-auto">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="text-warning text-center fw-bold">Ανακοινώσεις Εξέτασης</h2>
                        <p class="text-center text-secondary mb-4">
                            Παρακάτω εμφανίζονται οι ανακοινώσεις για την εξέταση διπλωματικών
                        </p>
                        <div class="row g-3 align-items-center mb-4">
                            <div class="col-md-6">
                                <label for="start-date" class="form-label">Από Ημερομηνία</label>
                                <input type="date" id="start-date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="end-date" class="form-label">Έως Ημερομηνία</label>
                                <input type="date" id="end-date" class="form-control">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Φοιτητής</th>
                                        <th>Διπλωματική</th>
                                        <th>Ημερομηνία Εξέτασης</th>
                                        <th>Τοποθεσία</th>
                                        <th>Περιγραφή</th>
                                    </tr>
                                </thead>
                                <tbody id="announcements-table-body">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Δεν βρέθηκαν ανακοινώσεις.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="loading-announcements" class="text-center my-3" style="display: none;">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Φόρτωση...</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group" role="group">
                                <button id="export-json" class="btn btn-warning">Εξαγωγή σε JSON</button>
                                <button id="export-xml" class="btn btn-secondary">Εξαγωγή σε XML</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="text-primary text-center fw-bold">Ολοκληρωμένες Διπλωματικές</h2>
                        <p class="text-center text-secondary mb-4">Παρακάτω εμφανίζονται οι ολοκληρωμένες διπλωματικές</p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Θέμα</th>
                                        <th>Καθηγητής</th>
                                        <th>Φοιτητής</th>
                                        <th>Σύνδεσμος</th>
                                    </tr>
                                </thead>
                                <tbody id="completed-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Δεν βρέθηκαν ολοκληρωμένες διπλωματικές.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="loading" class="text-center my-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Φόρτωση...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification-container" id="notification-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../globally_accessed/notifications/notifications.js"></script>
    <script src="guest.js"></script>
    <script src="../globally_accessed/dark-mode/switch_script.js"></script>
    <?php include "../globally_accessed/footer.html"; ?>
    
</body>
</html>

