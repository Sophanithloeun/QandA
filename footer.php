<?php
// footer.php
?>
    </div> <footer>
        <p>Q&A System &copy; <?php echo date('Y'); ?></p>
    </footer>
</body>
</html>
<?php
// Close the database connection if it's open
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>
