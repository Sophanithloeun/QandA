<?php
// index.php
// This page displays a list of all questions.
require_once 'db_config.php'; // Ensures session_start() and $mysqli are available
include 'header.php';
// Handle potential messages from other pages (e.g., after deleting a question)
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<h2>All Questions</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php
// Fetch all questions along with the username of the asker
$sql = "SELECT q.id, q.title, q.created_at, u.username 
        FROM questions q
        JOIN users u ON q.user_id = u.id
        ORDER BY q.created_at DESC";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0):
    while ($question = $result->fetch_assoc()):
?>
    <div class="question">
        <h3><a href="view_question.php?id=<?php echo $question['id']; ?>"><?php echo htmlspecialchars($question['title']); ?></a></h3>
        <p class="meta">Asked by <?php echo htmlspecialchars($question['username']); ?> on <?php echo date('F j, Y, g:i a', strtotime($question['created_at'])); ?></p>
        <a href="view_question.php?id=<?php echo $question['id']; ?>" class="btn btn-secondary">View Details & Answer</a>
    </div>
<?php
    endwhile;
else:
?>
    <p>No questions have been asked yet. <?php if(isset($_SESSION['user_id'])) { echo '<a href="ask_question.php">Be the first to ask one!</a>'; } else { echo '<a href="login.php">Login</a> to ask a question.'; } ?></p>
<?php
endif;

if ($result) {
    $result->free(); // Free result set
}

include 'footer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<script src="https://js.puter.com/v2/"></script>
    <script>
        (async () => {
            // (1) Create a random directory
            let dirName = puter.randName();
            await puter.fs.mkdir(dirName)

            // (2) Create 'index.html' in the directory with the contents "Hello, world!"
            await puter.fs.write(`${dirName}/index.html`, '<h1>Hello, world!</h1>');

            // (3) Host the directory under a random subdomain
            let subdomain = puter.randName();
            const site = await puter.hosting.create(subdomain, dirName)

            puter.print(`Website hosted at: <a href="https://${site.subdomain}.puter.site" target="_blank">https://${site.subdomain}.puter.site</a>`);
        })();
    </script>

</body>
</html>
