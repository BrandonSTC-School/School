<!DOCTYPE html>
<html>
<body>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "GameDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM Games";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Title</th><th>Genre</th><th>Rating</th><th>Price $</th></tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["GameId"] . "</td>";
        echo "<td>" . $row["Name"] . "</td>";
        echo "<td>" . $row["Genre"] . "</td>";
        echo "<td>" . $row["Rating"] . "</td>";
        echo "<td>" . $row["Price"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No games found.";
}

$conn->close();
?>

</body>
</html>