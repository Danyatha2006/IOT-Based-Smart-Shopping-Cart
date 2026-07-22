<?php
// Set response header to JSON
header("Content-Type: application/json");

// Define the absolute path to zbarimg.exe for XAMPP/Windows
// !!! YOU MUST REPLACE THIS PATH WITH YOUR ACTUAL ZBAR INSTALLATION LOCATION !!!
$zbar_path = "C:\\zbar\\zbarimg.exe"; 
// Example: If it's in C:\Program Files\ZBar\bin, use: "C:\\Program Files\\ZBar\\bin\\zbarimg.exe"

// --- 0. Database Connection and Error Check ---
include("db_config.php"); 
if (!isset($conn) || $conn->connect_error) {
    // Log the error for debugging (optional)
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["status" => "error", "message" => "Database connection failed. Check db_config.php"]);
    exit;
}

$response = ["status" => "error", "message" => "General error."];
$uploaded_file_path = '';
$barcode = ''; // Initialize barcode variable

// --- 1. HANDLE JPEG FILE UPLOAD (from ESP32's multipart POST) ---
if (isset($_FILES["imageFile"]) && $_FILES["imageFile"]["error"] == UPLOAD_ERR_OK) {
    
    // Use the temporary directory for saving the uploaded image
    $target_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR; 
    $temp_filename = uniqid('barcode_scan_', true) . '.jpg';
    $uploaded_file_path = $target_dir . $temp_filename;

    // Move the uploaded temporary file to the final destination
    if (move_uploaded_file($_FILES["imageFile"]["tmp_name"], $uploaded_file_path)) {
        
        // --- 2. DECODE BARCODE/QR CODE USING ZBAR COMMAND ---
        // Uses the absolute path defined at the top for Windows compatibility
        $command = $zbar_path . " -q --raw " . escapeshellarg($uploaded_file_path) . " 2>&1";
        $output = [];
        $return_var = 0;
        
        // Execute ZBar
        exec($command, $output, $return_var);
        
        // --- 3. PROCESS DECODING RESULT ---
        if ($return_var === 0 && !empty($output)) {
            $barcode = trim($output[0]); // The decoded string is the first line of output
            
            // --- 4. SEARCH IN DATABASE using Prepared Statements ---
            // Prepare statement to prevent SQL Injection
            $stmt = $conn->prepare("SELECT barcode, name, mrp, final_price, expiry_date FROM products WHERE barcode = ? LIMIT 1");
            
            // Bind the decoded barcode string as a parameter
            $stmt->bind_param("s", $barcode); // "s" denotes string type
            
            // Execute the query
            $stmt->execute();
            
            // Get the result
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();

                $response = [
                    "status"        => "success",
                    "barcode"       => $row["barcode"],
                    "name"          => $row["name"],
                    "mrp"           => $row["mrp"],
                    "final_price"   => $row["final_price"],
                    "expiry_date"   => $row["expiry_date"],
                    // Include all necessary fields from your database schema here
                ];
            } else {
                $response = ["status" => "not_found", "message" => "Barcode not found in database: " . $barcode];
            }
            
            $stmt->close(); // Close the statement
            
        } else {
            // Decoding failed
            $response = ["status" => "decode_fail", "message" => "No barcode or QR code detected in image."];
        }

    } else {
        $response["message"] = 'Error: Failed to save uploaded image on the server.';
    }

} else {
    // File upload failed (e.g., no file sent or size limit exceeded)
    $response["message"] = 'Error: No file uploaded or upload failed. Check POST data format.';
}

// --- 5. CLEANUP and OUTPUT ---
// Delete the temporary image file
if (file_exists($uploaded_file_path)) {
    @unlink($uploaded_file_path);
}

// Close DB connection
$conn->close();

// Send JSON response back to the ESP32
echo json_encode($response);
?>