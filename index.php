<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
// any stupid coment 
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

//HERE IS LAB18

$host = 'localhost'; 
$dbname = 'jugadores'; 
$user = 'julian'; 
$pass = 'julian';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle book search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT name, position, club FROM player WHERE name LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name']) && isset($_POST['position']) && isset($_POST['club'])) {
        // Insert new entry
        $name = htmlspecialchars($_POST['name']);
        $position = htmlspecialchars($_POST['position']);
        $club = htmlspecialchars($_POST['club']);
        
        $insert_sql = 'INSERT INTO player (name, position, club) VALUES (:name, :position, :club)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['name' => $name, 'position' => $position, 'club' => $club]);
    } elseif (isset($_POST['delete_name'])) {
        // Delete an entry
        $delete_name = $_POST['delete_name'];

        $delete_sql = 'DELETE FROM jugadores.player WHERE name = :name';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['name' => $name]);
    }
}

// Get all books for main table
$sql = 'SELECT name, position, club FROM jugadores.player';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Players Database</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
    <div class="hero-overlay">
        <h1 class="hero-title">Best Argentinian Players</h1>
        <h2 class="hero-subtitle">Add or delete players from the best Argentinian soccer league</h2>
    </div>
</div>


    <!-- Form section with container -->
    <div class="form-container">
        <h2>Add a new player </h2>
        <form action="index.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="nae" name="name" required>
            <br><br>
            <label for="position">Position:</label>
            <input type="text" id="position" name="position" required>
            <br><br>
            <label for="club">Club:</label>
            <input type="text" id="club" name="club" required>
            <br><br>
            <input type="submit" value="Add">
        </form>
    </div>
        

    <!-- Table section with container -->
    <div class="table-container">
        <h2>Top Argentinian Soccer Players</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Club</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td><?php echo htmlspecialchars($row['club']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a player to Ban</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by player:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Player</th>
                                    <th>Position</th>
                                    <th>Club</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><?php echo htmlspecialchars($row['club']); ?></td>
                                    <td>
                                        <form action="index.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_name" value="<?php echo $row['name']; ?>">
                                            <input type="submit" value="Ban!">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No players found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>