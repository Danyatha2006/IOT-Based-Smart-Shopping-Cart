<?php
include('db_config.php'); // connect to your database

if (isset($_GET['barcode']) && isset($_GET['name']) && isset($_GET['brand']) && isset($_GET['category']) && isset($_GET['mrp'])) {
    
    $barcode = $_GET['barcode'];
    $name = $_GET['name'];
    $brand = $_GET['brand'];
    $category = $_GET['category'];
    $mrp = $_GET['mrp'];
    $discount = isset($_GET['discount']) ? $_GET['discount'] : 0;
    $final_price = isset($_GET['final_price']) ? $_GET['final_price'] : $mrp;
    $pack_size = isset($_GET['pack_size']) ? $_GET['pack_size'] : '';
    $expiry_date = isset($_GET['expiry_date']) ? $_GET['expiry_date'] : NULL;
    $sponsored_flag = isset($_GET['sponsored_flag']) ? $_GET['sponsored_flag'] : 0;
    $store_margin = isset($_GET['store_margin']) ? $_GET['store_margin'] : 0;

    $sql = "INSERT INTO products (barcode, name, brand, category, mrp, discount, final_price, pack_size, expiry_date, sponsored_flag, store_margin)
            VALUES ('$barcode', '$name', '$brand', '$category', '$mrp', '$discount', '$final_price', '$pack_size', '$expiry_date', '$sponsored_flag', '$store_margin')";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Product inserted successfully!";
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "⚠️ Missing required input!";
}

$conn->close();
?>

