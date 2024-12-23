<?php
session_start();
require_once 'config.php';

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

function addBabyDetails($conn, $userId, $name, $dob, $weight, $height) {
    $sql = "INSERT INTO baby_details (user_id, name, date_of_birth, weight, height) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdd", $userId, $name, $dob, $weight, $height);
    return $stmt->execute();
}

function updateBabyDetails($conn, $babyId, $name, $dob, $weight, $height) {
    $sql = "UPDATE baby_details SET name = ?, date_of_birth = ?, weight = ?, height = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddi", $name, $dob, $weight, $height, $babyId);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    if (isset($_POST['add_baby'])) {
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        $userId = $_SESSION['user_id'];
        
        if (addBabyDetails($conn, $userId, $name, $dob, $weight, $height)) {
            $message = "Baby details added successfully!";
        } else {
            $error = "Error adding baby details.";
        }
    } elseif (isset($_POST['update_baby'])) {
        $babyId = $_POST['baby_id'];
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        
        if (updateBabyDetails($conn, $babyId, $name, $dob, $weight, $height)) {
            $message = "Baby details updated successfully!";
        } else {
            $error = "Error updating baby details.";
        }
    }
}

$babyDetails = [];
if ($loggedIn) {
    $sql = "SELECT * FROM baby_details WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $babyDetails[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Shield - Services</title>
    <link rel="stylesheet" href="css/services.css">
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
        <section class="notifications">
            <h2>Latest Vaccine Notifications</h2>
            <p>Stay up to date with the latest vaccine information for your child.</p>
            <button id="getNotificationBtn" class="btn"><i class="fas fa-bell"></i> Get Notifications</button>
        </section>

        <section class="baby-details">
            <h2>Baby Details</h2>
            <button id="addBabyBtn" class="btn"><i class="fas fa-plus"></i> Add Baby</button>
            
            <?php if (!empty($babyDetails)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Weight</th>
                            <th>Height</th>
                            <th>Action</th>
                            <th>Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($babyDetails as $baby): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($baby['name']); ?></td>
                                <td><?php echo htmlspecialchars($baby['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($baby['weight']); ?> kg</td>
                                <td><?php echo htmlspecialchars($baby['height']); ?> cm</td>
                                <td>
                                    <button class="update-btn" 
                                            data-id="<?php echo $baby['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($baby['name']); ?>"
                                            data-dob="<?php echo htmlspecialchars($baby['date_of_birth']); ?>"
                                            data-weight="<?php echo htmlspecialchars($baby['weight']); ?>"
                                            data-height="<?php echo htmlspecialchars($baby['height']); ?>">
                                        Update
                                    </button>
                                </td>
                                <td><button class="view-record-btn" data-id="<?php echo $baby['id']; ?>">View Record</button></td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No baby details found. Add a new baby to get started!</p>
            <?php endif; ?>
        </section>
    </main>

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

    <div id="addBabyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Baby Details</h2>
            <form action="services.php" method="post">
                <input type="text" name="name" placeholder="Baby's Name" required>
                <input type="date" name="dob" required>
                <input type="number" name="weight" step="0.01" placeholder="Weight (kg)" required>
                <input type="number" name="height" step="0.01" placeholder="Height (cm)" required>
                <button type="submit" name="add_baby" class="btn">Add Baby</button>
            </form>
        </div>
    </div>

    <div id="updateBabyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Baby Details</h2>
            <form action="services.php" method="post">
                <input type="hidden" name="baby_id" id="update_baby_id">
                <input type="text" name="name" id="update_name" placeholder="Baby's Name" required>
                <input type="date" name="dob" id="update_dob" required>
                <input type="number" name="weight" id="update_weight" step="0.01" placeholder="Weight (kg)" required>
                <input type="number" name="height" id="update_height" step="0.01" placeholder="Height (cm)" required>
                <button type="submit" name="update_baby" class="btn">Update Baby</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addBabyBtn = document.getElementById('addBabyBtn');
            const addBabyModal = document.getElementById('addBabyModal');
            const updateBabyModal = document.getElementById('updateBabyModal');
            const closeBtns = document.getElementsByClassName('close');
            const getNotificationBtn = document.getElementById('getNotificationBtn');

            <?php if (!$loggedIn): ?>
            addBabyBtn.onclick = function() {
                alert('Please log in to add baby details.');
                window.location.href = 'login.php';
            }
            <?php else: ?>
            addBabyBtn.onclick = function() {
                addBabyModal.style.display = 'block';
            }
            <?php endif; ?>

            for (let closeBtn of closeBtns) {
                closeBtn.onclick = function() {
                    addBabyModal.style.display = 'none';
                    updateBabyModal.style.display = 'none';
                }
            }

            window.onclick = function(event) {
                if (event.target == addBabyModal) {
                    addBabyModal.style.display = 'none';
                }
                if (event.target == updateBabyModal) {
                    updateBabyModal.style.display = 'none';
                }
            }

            getNotificationBtn.onclick = function() {
                alert('You have successfully subscribed to notifications!');
            }

            const updateBtns = document.getElementsByClassName('update-btn');
            for (let btn of updateBtns) {
                btn.onclick = function() {
                    const babyId = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const dob = this.getAttribute('data-dob');
                    const weight = this.getAttribute('data-weight');
                    const height = this.getAttribute('data-height');

                    document.getElementById('update_baby_id').value = babyId;
                    document.getElementById('update_name').value = name;
                    document.getElementById('update_dob').value = dob;
                    document.getElementById('update_weight').value = weight;
                    document.getElementById('update_height').value = height;

                    updateBabyModal.style.display = 'block';
                }
            }


            const viewRecordBtns = document.getElementsByClassName('view-record-btn');
            for (let btn of viewRecordBtns) {
                btn.onclick = function() {
                    const babyId = this.getAttribute('data-id');
                    window.location.href = 'baby-record-book.php?baby_id=' + babyId;
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
