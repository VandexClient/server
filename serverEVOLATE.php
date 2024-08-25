<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function get_role($conn, $nickname, $password) {
    $sql = "SELECT role FROM users WHERE nickname = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nickname, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];
        if ($role == "default") {
            return "sub";
        } else {
            return $role;
        }
    } else {
        return "fail";
    }
}

function get_id($conn, $nickname) {
    $sql = "SELECT id FROM users WHERE nickname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        return "fail";
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'expire' && isset($_GET['nickname'])) {
        $nickname = $_GET['nickname'];
        $sql = "SELECT expiration_date FROM users WHERE `nickname` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nickname);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $expires_date = $row['expiration_date'];
            echo $expires_date;
        } else {
            echo "fail";
        }
    } elseif ($_GET['action'] === 'get_role' && isset($_GET['nickname']) && isset($_GET['password'])) {
        $nickname = $_GET['nickname'];
        $password = $_GET['password'];
        echo get_role($conn, $nickname, $password);
    } elseif ($_GET['action'] === 'get_id' && isset($_GET['nickname'])) {
        $nickname = $_GET['nickname'];
        echo get_id($conn, $nickname);
    } elseif ($_GET['action'] === 'authenticate' && isset($_GET['nickname']) && isset($_GET['password']) && isset($_GET['hwid'])) {
        $nickname = $_GET['nickname'];
        $password = $_GET['password'];
        $hwid = $_GET['hwid'];

        $sql = "SELECT * FROM users WHERE nickname = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nickname, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $db_hwid = $row['hwid'];

            if ($db_hwid == "") {
                $sql_update_hwid = "UPDATE users SET hwid = ? WHERE nickname = ? AND password = ?";
                $stmt_update_hwid = $conn->prepare($sql_update_hwid);
                $stmt_update_hwid->bind_param("sss", $hwid, $nickname, $password);
                if ($stmt_update_hwid->execute()) {
                    echo "hwid updated";
                } else {
                    echo "Error updating record";
                }
            } elseif ($db_hwid == $hwid) {
                echo "passed";
            } else {
                echo "hwid";
            }
        } else {
            echo "fail"; // Если сочетание nickname и password не найдено
        }
    }
}

$conn->close();
?>