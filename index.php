<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["fileToUpload"])) {
    $targetDirectory = "files/";
    $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    $randomFilename = generateRandomFilename(5) . '.' . $fileType;
    $targetFile = $targetDirectory . $randomFilename;

    $uploadOk = 1;

    // Check file size (You can adjust the maximum file size according to your needs)
    if ($_FILES["fileToUpload"]["size"] > 5000000) { // 5 MB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats (You can add more formats if needed)
    $allowedFormats = array("jpg", "png", "gif", "pdf");
    if (!in_array($fileType, $allowedFormats)) {
        echo "Sorry, only JPG, PNG, GIF, and PDF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Your file was not uploaded.";
    } else {
        $fileHash = hash_file('sha256', $_FILES["fileToUpload"]["tmp_name"]);
        $duplicateFile = findDuplicateFile($fileHash, $targetDirectory);

        if ($duplicateFile !== false) {
            $downloadLink = "https://file.sayed.page/" . urlencode($duplicateFile);
            echo "This file already exists. You can download it at: <a href=\"$downloadLink\">$downloadLink</a>";
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
                $downloadLink = "https://file.sayed.page/" . urlencode($targetFile);
                echo "The file has been uploaded. Hash: $fileHash. You can download it at: <a href=\"$downloadLink\">$downloadLink</a>";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
    exit;
}

// Function to generate a random alphanumeric filename
function generateRandomFilename($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Function to find a duplicate file based on its hash
function findDuplicateFile($hash, $directory)
{
    $files = glob($directory . '*');
    foreach ($files as $file) {
        if (hash_file('sha256', $file) === $hash) {
            return $file;
        }
    }
    return false;
}










// Function to get the last 10 uploaded files based on their timestamps
function getLastUploadedFiles($directory, $count)
{
    $files = glob($directory . '*');
    
    // Sort the files based on their timestamps in descending order
    usort($files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $lastUploadedFiles = [];
    $counter = 0;
    
    foreach ($files as $file) {
        // Check if the current file is a file (not a directory)
        if (is_file($file)) {
            $lastUploadedFiles[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'timestamp' => filemtime($file),
            ];
            $counter++;
        }
        
        if ($counter >= $count) {
            break;
        }
    }
    
    return $lastUploadedFiles;
}




























?>

<!DOCTYPE html>
<html>
<head>
    <title>File Uploader</title>
</head>
<body>
    <h1>File Upload</h1>
    <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload File" name="submit">
    </form>

    <h2>Last 10 Uploaded Files</h2>
    <table>
        <tr>
            <th>Serial No</th>
            <th>File Name</th>
            <th>File Size</th>
            <th>TimeStamp</th>
        </tr>
        <?php
        $uploadedFiles = getLastUploadedFiles("files/", 10);
        $serialNo = 1;
        foreach ($uploadedFiles as $file) {
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $timeStamp = date('Y-m-d H:i:s', $file['timestamp']);
            $fileLink = "https://file.sayed.page/" . urlencode("files/" . $fileName);
            echo "<tr>
                    <td>$serialNo</td>
                    <td><a href=\"$fileLink\" target=\"_blank\">$fileName</a></td>
                    <td>$fileSize</td>
                    <td>$timeStamp</td>
                  </tr>";
            $serialNo++;
        }
        ?>
    </table>
</body>
</html>

