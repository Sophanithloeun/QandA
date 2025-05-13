<?php
// view_question.php
require_once 'db_config.php'; // Ensures session_start() and $mysqli are available
include 'header.php';

$errors = [];
$success_message = "";

// Check if question ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='alert alert-danger'>Invalid question ID.</p>";
    include 'footer.php';
    exit;
}
$question_id = intval($_GET['id']);

// Handle potential messages from other actions (e.g., successful answer submission)
if (isset($_GET['message'])) {
    $success_message = htmlspecialchars($_GET['message']);
}


// Handle answer submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_answer'])) {
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "You must be logged in to submit an answer.";
        // It's better to redirect to login, but for simplicity, showing an error here.
        // header("Location: login.php?redirect=view_question.php?id=" . $question_id);
        // exit;
    } else {
        $answer_body = trim($_POST['answer_body']);
        $user_id = $_SESSION['user_id'];

        if (empty($answer_body)) {
            $errors[] = "Answer body cannot be empty.";
        }

        if (empty($errors)) {
            $stmt = $mysqli->prepare("INSERT INTO answers (question_id, user_id, body) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $question_id, $user_id, $answer_body);
            if ($stmt->execute()) {
                $stmt->close();
                // Redirect to the same page to show the new answer and prevent form resubmission
                header("Location: view_question.php?id=" . $question_id . "&message=Answer+submitted+successfully!");
                exit;
            } else {
                $errors[] = "Failed to submit answer. Error: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}


// Fetch the question details
$stmt_q = $mysqli->prepare("SELECT q.id, q.title, q.created_at, u.username AS asker_username, q.user_id AS question_user_id
                           FROM questions q
                           JOIN users u ON q.user_id = u.id
                           WHERE q.id = ?");
$stmt_q->bind_param("i", $question_id);
$stmt_q->execute();
$result_q = $stmt_q->get_result();

if ($question = $result_q->fetch_assoc()):
?>
    <div class="question">
        <h2><?php echo htmlspecialchars($question['title']); ?></h2>
        <p class="meta">Asked by <?php echo htmlspecialchars($question['asker_username']); ?> on <?php echo date('F j, Y, g:i a', strtotime($question['created_at'])); ?></p>
        
        <?php
        // Admin or original poster can delete the question
        if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $question['question_user_id']))):
        ?>
            <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this question and all its answers?');">
                <input type="hidden" name="action" value="delete_question">
                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete Question</button>
            </form>
        <?php endif; ?>
    </div>

    <hr>
    <h3>Answers</h3>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($errors) && isset($_POST['submit_answer'])): // Show answer submission errors ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <?php
    // Fetch answers for this question
    $stmt_a = $mysqli->prepare("SELECT a.id AS answer_id, a.body AS answer_body, a.created_at AS answer_created_at, u.username AS answerer_username, a.user_id AS answer_user_id
                               FROM answers a
                               JOIN users u ON a.user_id = u.id
                               WHERE a.question_id = ?
                               ORDER BY a.created_at ASC");
    $stmt_a->bind_param("i", $question_id);
    $stmt_a->execute();
    $result_a = $stmt_a->get_result();

    if ($result_a->num_rows > 0):
        while ($answer = $result_a->fetch_assoc()):
    ?>
        <div class="answer">
            <p><?php echo nl2br(htmlspecialchars($answer['answer_body'])); ?></p>
            <p class="meta">Answered by <?php echo htmlspecialchars($answer['answerer_username']); ?> on <?php echo date('F j, Y, g:i a', strtotime($answer['answer_created_at'])); ?></p>
            <?php
            // Admin or original poster can delete the answer
            if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $answer['answer_user_id']))):
            ?>
                <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this answer?');">
                    <input type="hidden" name="action" value="delete_answer">
                    <input type="hidden" name="answer_id" value="<?php echo $answer['answer_id']; ?>">
                    <input type="hidden" name="question_id_redirect" value="<?php echo $question_id; ?>"> <button type="submit" class="btn btn-danger btn-sm">Delete Answer</button>
                </form>
            <?php endif; ?>
        </div>
    <?php
        endwhile;
    else:
        echo "<p>No answers yet. Be the first to answer!</p>";
    endif;
    $stmt_a->close();
    ?>

    <hr>
    <?php if (isset($_SESSION['user_id'])): ?>
        <h4>Submit Your Answer</h4>
        <form action="view_question.php?id=<?php echo $question_id; ?>" method="post">
            <div class="form-group">
                <textarea name="answer_body" rows="5" class="form-control" required placeholder="Type your answer here..."></textarea>
            </div>
            <button type="submit" name="submit_answer" class="btn">Submit Answer</button>
        </form>
    <?php else: ?>
        <p><a href="login.php?redirect=view_question.php?id=<?php echo $question_id; ?>">Login</a> to submit an answer.</p>
    <?php endif; ?>

<?php
else:
    echo "<p class='alert alert-danger'>Question not found.</p>";
endif;
$stmt_q->close();

include 'footer.php';
?>
<link rel="stylesheet" href="css/style.css">
