<?php
// test_file.php - Correct version
$filename = '6939f6e81aacb_6939aabb7ecdf_Master_Survey_Instrument__1_.pdf';

// Use local file system path, not URL
$uploadPath = __DIR__ . '/../../authentication/uploads/';

echo "Checking file: " . htmlspecialchars($filename) . "<br>";
echo "Upload path: " . htmlspecialchars($uploadPath) . "<br>";

// Clean the path
$uploadPath = realpath($uploadPath);
if ($uploadPath === false) {
    $uploadPath = __DIR__ . '/../../authentication/uploads/';
}

echo "Real upload path: " . htmlspecialchars($uploadPath) . "<br>";

$fullPath = $uploadPath . '/' . $filename;
echo "Full path to check: " . htmlspecialchars($fullPath) . "<br>";

if (file_exists($fullPath)) {
    echo "✅ File exists!<br>";
    
    // Check file permissions
    echo "File size: " . filesize($fullPath) . " bytes<br>";
    echo "Readable: " . (is_readable($fullPath) ? "✅ Yes" : "❌ No") . "<br>";
    echo "URL to access: http://localhost/OAMS-ZPPSU/authentication/uploads/" . $filename . "<br>";
    
    // Test if URL is accessible
    $testUrl = "http://localhost/OAMS-ZPPSU/authentication/uploads/" . $filename;
    echo "<br>Testing URL access:<br>";
    echo "<a href='" . htmlspecialchars($testUrl) . "' target='_blank'>Click here to test URL</a><br>";
    
} else {
    echo "❌ File NOT found!<br>";
    
    // Check if directory exists
    if (is_dir($uploadPath)) {
        echo "✅ Upload directory exists<br>";
        
        // List files in uploads directory
        echo "<br>Files in uploads directory:<br>";
        $files = scandir($uploadPath);
        if ($files === false) {
            echo "❌ Cannot read directory<br>";
        } else {
            $fileCount = 0;
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "- " . htmlspecialchars($file) . " (" . filesize($uploadPath . '/' . $file) . " bytes)<br>";
                    $fileCount++;
                }
            }
            if ($fileCount === 0) {
                echo "No files found in uploads directory<br>";
            }
        }
    } else {
        echo "❌ Upload directory doesn't exist!<br>";
        echo "Creating uploads directory...<br>";
        
        // Try to create the directory
        if (mkdir($uploadPath, 0755, true)) {
            echo "✅ Created uploads directory<br>";
        } else {
            echo "❌ Failed to create directory<br>";
        }
    }
}

// Also test the actual URL
echo "<hr>";
echo "<h3>URL Test</h3>";
$testUrl = "http://localhost/OAMS-ZPPSU/authentication/uploads/" . $filename;
echo "Testing URL: <a href='" . htmlspecialchars($testUrl) . "' target='_blank'>" . htmlspecialchars($testUrl) . "</a><br>";
?>