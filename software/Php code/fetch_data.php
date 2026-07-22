<?php
include("db_config.php");

// Fetch latest 10 products
$sql = "SELECT product_id, barcode, name, brand, category, final_price, expiry_date FROM products ORDER BY product_id DESC LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<h2>Latest Products</h2>";
  echo "<table border='1' cellpadding='8'>";
  echo "<tr>
          <th>ID</th>
          <th>Barcode</th>
          <th>Name</th>
          <th>Brand</th>
          <th>Category</th>
          <th>Price (₹)</th>
          <th>Expiry Date</th>
        </tr>";
  
  while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['product_id']}</td>
            <td>{$row['barcode']}</td>
            <td>{$row['name']}</td>
            <td>{$row['brand']}</td>
            <td>{$row['category']}</td>
            <td>{$row['final_price']}</td>
            <td>{$row['expiry_date']}</td>
          </tr>";
  }

  echo "</table>";
} else {
  echo "No products found in the database.";
}

$conn->close();
?>
