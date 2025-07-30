<?php
// Připojení k databázi
$pdo = new PDO("mysql:host=localhost;dbname=table;charset=utf8", "root", "");

// Import CSV
if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    $csvFile = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // přeskočíme hlavičku
        fgetcsv($handle, 1000, ";");
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $stmt = $pdo->prepare("INSERT INTO osoby (datum, jmeno, email, mesto) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data[0], $data[1], $data[2], $data[3]]);
        }
        fclose($handle);
        echo "<p style='color:green'>Import dokončen.</p>";
    }
}

// Export CSV
if (isset($_POST['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');
    $output = fopen("php://output", "w");
    fputcsv($output, ['Datum', 'Jméno', 'E-mail', 'Město'], ';');

    $stmt = $pdo->query("SELECT datum, jmeno, email, mesto FROM osoby");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ';');
    }
    fclose($output);
    exit;
}

// Načtení dat
$stmt = $pdo->query("SELECT * FROM osoby ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Seznam osob</h2>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit" name="import">Import CSV</button>
    <button type="submit" name="export">Export CSV</button>
</form>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Datum</th>
            <th>Jméno</th>
            <th>Email</th>
            <th>Město</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['datum']) ?></td>
                <td><?= htmlspecialchars($row['jmeno']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['mesto']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
?>
