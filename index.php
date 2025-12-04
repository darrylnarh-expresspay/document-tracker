<?php
require_once 'config.php';

require_once __DIR__ . '/vendor/autoload.php';

use App\Repository\GoogleSheetRepository;
use App\Services\S3FileUploader;
use App\Services\DocumentManager;
use App\Services\OwnCloudFileUploader;

// initialize services

// storage for document data
$storage = new GoogleSheetRepository(GOOGLE_CREDS_PATH, SPREADSHEET_ID);

// uploader for documenrs
// [ ] enable the one to be used

/*
// S3 Bucket
$s3Client = new \Aws\S3\S3Client([
    'region'      => AWS_REGION,
    'version'     => AWS_VERSION,
    'credentials' => [
        'key'    => AWS_KEY,
        'secret' => AWS_SECRET,
    ],
]);
$uploader = new S3FileUploader($s3Client, S3_BUCKET);

// OwnCluod WebDAV  
$uploader = new OwnCloudFileUploader(
    WEBDAV_BASE_URI,
    WEBDAV_USER,
    WEBDAV_PASSWORD
);
*/

// load data
$docs = $storage->getAll();

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'];
    $redirect = true;

    if ($mode === 'delete') {
        $storage->delete((int)$_POST['row']);
    } 
    elseif ($mode === 'create') {
        $fileUrl = $uploader->upload($_FILES['document'], $_POST['name']);
        $rowData = DocumentManager::prepareRow($_POST, $fileUrl);
        $storage->create($rowData);
    } 
    elseif ($mode === 'update') {
        $rowIndex = (int)$_POST['row'];
        $existingRow = $docs[$rowIndex] ?? [];
        $existingUrl = $existingRow[7] ?? "";

        // determine if uploading new or keeping old
        if (!empty($_FILES['editdocument']['name'])) {
            $fileUrl = $uploader->upload($_FILES['editdocument'], $_POST['name']);
        } else {
            $fileUrl = "";
        }

        $rowData = DocumentManager::prepareRow($_POST, $fileUrl, $existingUrl);
        // [ ] match id to original if needed, or generate new. 
        // [ ] prepareRow generates a new ID. If you want to keep the old ID:
        $rowData[0] = $existingRow[0]; 

        $storage->update($rowIndex, $rowData);
    }

    if($redirect) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// html helpers
function getRowClass($status) {
    switch ($status) {
        case 'EXPIRED': return 'table-danger';
        case 'REVIEW': return 'table-warning';
        case 'PERPETUAL': return 'table-info';
        default: return 'table-success';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= APP_TITLE ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<h3 class="mb-3"><?= APP_TITLE ?></h3>
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Create</button>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <?php if(!empty($docs)): ?>
                <?php foreach ($docs[0] as $header): ?>
                    <th><?= htmlspecialchars(ucfirst($header)) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach (array_slice($docs, 1) as $index => $row): ?>
            <?php 
                // Adjust index because we sliced the header off
                $realIndex = $index + 1; 
                $status = strtoupper($row[6] ?? ''); 
            ?>
            <tr class="<?= getRowClass($status) ?>">
                <?php foreach ($row as $colIndex => $cell): ?>
                    <td>
                        <?php if ($colIndex === 7 && !empty($cell)): ?>
                            <a href="<?= htmlspecialchars($cell) ?>" target="_blank">Open Doc</a>
                        <?php else: ?>
                            <?= htmlspecialchars($cell) ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <td>
                    <button class="btn btn-outline-primary btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal" 
                            data-row="<?= $realIndex ?>">Edit</button>
                    <button class="btn btn-outline-danger btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal" 
                            data-row="<?= $realIndex ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// load row into edit modal
var docs = <?= json_encode($docs) ?>;

var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    var row = btn.getAttribute('data-row');

    document.getElementById('editRow').value = row;
    document.getElementById('editName').value = docs[row][1];
    document.getElementById('editCategory').value = docs[row][2];
    document.getElementById('editEffective').value = docs[row][3];
    document.getElementById('editExpiry').value = docs[row][4];
    document.getElementById('editPerpetual').value = docs[row][5];
});

// delete modal row
var deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    var row = btn.getAttribute('data-row');
    document.getElementById('deleteRow').value = row;
});
</script>
</body>
</html>