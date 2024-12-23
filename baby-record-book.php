<?php
session_start();
require_once 'config.php';

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

if (!isset($_GET['baby_id'])) {
    header("Location: services.php");
    exit();
}

$babyId = $_GET['baby_id'];

$stmt = $conn->prepare("SELECT * FROM baby_details WHERE id = ?");
$stmt->bind_param("i", $babyId);
$stmt->execute();
$babyResult = $stmt->get_result();
$baby = $babyResult->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM vaccinations WHERE baby_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $babyId);
$stmt->execute();
$vaccinationResult = $stmt->get_result();
$vaccinations = $vaccinationResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vaccine'])) {
    $date = $_POST['date'];
    $vaccineName = $_POST['vaccine_name'];
    $dose = $_POST['dose'];

    $stmt = $conn->prepare("INSERT INTO vaccinations (baby_id, date, vaccine_name, dose) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $babyId, $date, $vaccineName, $dose);
    $stmt->execute();
    header("Location: baby-record-book.php?baby_id=" . $babyId);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vaccine'])) {
    $vaccineId = $_POST['vaccine_id'];
    $date = $_POST['date'];
    $vaccineName = $_POST['vaccine_name'];
    $dose = $_POST['dose'];

    $stmt = $conn->prepare("UPDATE vaccinations SET date = ?, vaccine_name = ?, dose = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $date, $vaccineName, $dose, $vaccineId);
    $stmt->execute();
    header("Location: baby-record-book.php?baby_id=" . $babyId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Record Book - Child Shield</title>
    <link rel="stylesheet" href="css/baby-record-book.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="https://kit.fontawesome.com/20f08145b4.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Child Shield</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php" class="active">Services</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="user-actions">
                <?php if ($loggedIn): ?>
                    <span class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                    <a href="logout.php" class="btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="baby-details">
            <h1><?php echo htmlspecialchars($baby['name']); ?>'s Record Book</h1>
            <div class="baby-info">
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($baby['date_of_birth']); ?></p>
                <p><strong>Weight:</strong> <?php echo htmlspecialchars($baby['weight']); ?> kg</p>
                <p><strong>Height:</strong> <?php echo htmlspecialchars($baby['height']); ?> cm</p>
            </div>
        </section>

        <section class="vaccination-records">
            <h2>Vaccination Records</h2>
            <button id="addVaccineBtn" class="btn"><i class="fas fa-plus"></i> Add Vaccine</button>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vaccine Name</th>
                        <th>Dose (mg)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vaccinations as $vaccine): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vaccine['date']); ?></td>
                            <td><?php echo htmlspecialchars($vaccine['vaccine_name']); ?></td>
                            <td><?php echo htmlspecialchars($vaccine['dose']); ?></td>
                            <td>
                                <button class="update-btn" 
                                        data-id="<?php echo $vaccine['id']; ?>"
                                        data-date="<?php echo htmlspecialchars($vaccine['date']); ?>"
                                        data-name="<?php echo htmlspecialchars($vaccine['vaccine_name']); ?>"
                                        data-dose="<?php echo htmlspecialchars($vaccine['dose']); ?>">
                                    Update
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="addVaccineModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Vaccine</h2>
            <form action="baby-record-book.php?baby_id=<?php echo $babyId; ?>" method="post">
                <input type="date" name="date" required>
                <input type="text" name="vaccine_name" placeholder="Vaccine Name" required>
                <input type="number" name="dose" step="0.01" placeholder="Dose (mg)" required>
                <button type="submit" name="add_vaccine" class="btn">Add Vaccine</button>
            </form>
        </div>
    </div>

    <div id="updateVaccineModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Vaccine</h2>
            <form action="baby-record-book.php?baby_id=<?php echo $babyId; ?>" method="post">
                <input type="hidden" name="vaccine_id" id="update_vaccine_id">
                <input type="date" name="date" id="update_date" required>
                <input type="text" name="vaccine_name" id="update_vaccine_name" placeholder="Vaccine Name" required>
                <input type="number" name="dose" id="update_dose" step="0.01" placeholder="Dose (mg)" required>
                <button type="submit" name="update_vaccine" class="btn">Update Vaccine</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@childshield.com</p>
                <p>Phone: (+94) 456-7890</p>
                <p>Address: New kandy road, Malabe, Colombo</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-section calendar-section">
                <div id="calendar"></div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Child Shield. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addVaccineBtn = document.getElementById('addVaccineBtn');
            const addVaccineModal = document.getElementById('addVaccineModal');
            const updateVaccineModal = document.getElementById('updateVaccineModal');
            const closeBtns = document.getElementsByClassName('close');

            addVaccineBtn.onclick = function() {
                addVaccineModal.style.display = 'block';
            }

            for (let closeBtn of closeBtns) {
                closeBtn.onclick = function() {
                    addVaccineModal.style.display = 'none';
                    updateVaccineModal.style.display = 'none';
                }
            }

            window.onclick = function(event) {
                if (event.target == addVaccineModal) {
                    addVaccineModal.style.display = 'none';
                }
                if (event.target == updateVaccineModal) {
                    updateVaccineModal.style.display = 'none';
                }
            }

            const updateBtns = document.getElementsByClassName('update-btn');
            for (let btn of updateBtns) {
                btn.onclick = function() {
                    const vaccineId = this.getAttribute('data-id');
                    const date = this.getAttribute('data-date');
                    const name = this.getAttribute('data-name');
                    const dose = this.getAttribute('data-dose');

                    document.getElementById('update_vaccine_id').value = vaccineId;
                    document.getElementById('update_date').value = date;
                    document.getElementById('update_vaccine_name').value = name;
                    document.getElementById('update_dose').value = dose;

                    updateVaccineModal.style.display = 'block';
                }
            }
        });
        function generateCalendar() {
            const today = new Date();
            const month = today.getMonth();
            const year = today.getFullYear();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            let calendarHTML = '<table>';
            calendarHTML += '<tr><th colspan="7">' + today.toLocaleString('default', { month: 'long' }) + ' ' + year + '</th></tr>';
            calendarHTML += '<tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr>';

            let day = 1;
            for (let i = 0; i < 6; i++) {
                calendarHTML += '<tr>';
                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < new Date(year, month, 1).getDay()) {
                        calendarHTML += '<td></td>';
                    } else if (day > daysInMonth) {
                        break;
                    } else {
                        if (day === today.getDate()) {
                            calendarHTML += '<td class="today">' + day + '</td>';
                        } else {
                            calendarHTML += '<td>' + day + '</td>';
                        }
                        day++;
                    }
                }
                calendarHTML += '</tr>';
                if (day > daysInMonth) {
                    break;
                }
            }
            calendarHTML += '</table>';

            document.getElementById('calendar').innerHTML = calendarHTML;
        }

        document.addEventListener('DOMContentLoaded', function() {
            generateCalendar();
        });
    </script>
</body>
</html>
