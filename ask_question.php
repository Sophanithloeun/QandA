<?php
// ask_question.php
require_once 'db_config.php'; // Ensures session_start() and $mysqli are available

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=ask_question.php");
    exit;
}

$title = "";
$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $user_id = $_SESSION['user_id'];

    if (empty($title)) {
        $errors[] = "Question title cannot be empty.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO questions (user_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $title);

        if ($stmt->execute()) {
            $question_id = $stmt->insert_id; // Get the ID of the newly inserted question
            $stmt->close();
            // Redirect to the view_question page for the new question
            header("Location: view_question.php?id=" . $question_id . "&message=Question+posted+successfully!");
            exit;
        } else {
            $errors[] = "Failed to post question. Please try again. Error: " . $stmt->error;
            $stmt->close();
        }
    }
}

include 'header.php';
?>

<h2>Ask a New Question</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="ask_question.php" method="post">
    <div class="form-group">
        <label for="title">Question Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </div>
    <button type="submit" class="btn">Submit Question</button>
</form>

<?php include 'footer.php'; ?>
<link rel="stylesheet" href="css/style.css">