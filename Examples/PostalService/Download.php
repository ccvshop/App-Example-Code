<?php
/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - First Draft
 *
 */
if (!isset($_GET['file'])) {
    header('HTTP/1.1 404 File Not Found', true, 404);
    die();
}

/**
 * A download script to present the 'Save As' dialog for the merchant is needed.
 * With this script the nescary headers are set to ensure a correct download.
 * If you deeplink directly to the file without setting these headers it won't work.
 */

$sFileName = urldecode($_GET['file']);

if ($sFileName == 'specimen_label.png') {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $sFileName . '";');
    echo file_get_contents($sFileName);
    die();
}

header('HTTP/1.1 404 File Not Found', true, 404);
die();