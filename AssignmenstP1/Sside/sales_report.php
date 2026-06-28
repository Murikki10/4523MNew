<?php
// Start session to track staff authorization status
session_start();

// Security Guard: Redirect to login if session does not exist
if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

// Include the existing database connection file approved by the schema
require_once('../conn.php');

if (!isset($conn)) {
  die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

// --- 1. Calculate Summary Dashboard Metrics (Only include statuses: 3=Approved, 4=Pending delivery, 5=Completed) ---

// Get Total Revenue directly from successful orders
$revenue_query = "SELECT SUM(ototalamount) AS grand_revenue FROM Orders WHERE ostatus IN (3, 4, 5)";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_row = mysqli_fetch_assoc($revenue_result);
$total_revenue_amount = !empty($revenue_row['grand_revenue']) ? floatval($revenue_row['grand_revenue']) : 0.00;

// Get Total Items/Units Sold across all validated orders
$units_query = "SELECT SUM(of.oqty) AS total_units FROM OrderFurnitures of JOIN Orders o ON of.oid = o.oid WHERE o.ostatus IN (3, 4, 5)";
$units_result = mysqli_query($conn, $units_query);
$units_row = mysqli_fetch_assoc($units_result);
$total_units_sold = !empty($units_row['total_units']) ? intval($units_row['total_units']) : 0;

// Find the Top Selling Product Name based on highest accumulated quantity sold
$top_product_query = "SELECT f.fname FROM OrderFurnitures of 
                      JOIN Furnitures f ON of.fid = f.fid 
                      JOIN Orders o ON of.oid = o.oid 
                      WHERE o.ostatus IN (3, 4, 5) 
                      GROUP BY f.fid 
                      ORDER BY SUM(of.oqty) DESC LIMIT 1";
$top_product_result = mysqli_query($conn, $top_product_query);
$top_product_name = "None";
if ($top_product_result && mysqli_num_rows($top_product_result) > 0) {
  $top_product_row = mysqli_fetch_assoc($top_product_result);
  $top_product_name = $top_product_row['fname'];
}

// --- 2. Fetch Detailed Sales Itemized Breakdown Row Lists ---
$report_query = "SELECT of.oid, of.fid, f.fname, f.fprice, of.oqty, (of.oqty * f.fprice) AS item_revenue
                 FROM OrderFurnitures of
                 JOIN Furnitures f ON of.fid = f.fid
                 JOIN Orders o ON of.oid = o.oid
                 WHERE o.ostatus IN (3, 4, 5)
                 ORDER BY of.oid DESC";
$report_result = mysqli_query($conn, $report_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Report - Premium Living Staff</title>
  <style>
    /* --- Shared Layout (Consistent with your current progress) --- */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      display: flex;
      background-color: #f4f7f6;
      min-height: 100vh;
    }

    .sidebar {
      width: 260px;
      background: #2c3e50;
      color: white;
      display: flex;
      flex-direction: column;
      position: fixed;
      height: 100%;
    }

    .sidebar .logo {
      font-size: 22px;
      text-align: center;
      font-weight: bold;
      padding: 30px 20px;
      background: #1a252f;
      border-bottom: 1px solid #34495e;
    }

    .sidebar .logo small {
      display: block;
      font-size: 14px;
      color: #1abc9c;
      margin-top: 5px;
    }

    .sidebar ul.navbar {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar ul.navbar li a {
      display: block;
      padding: 18px 25px;
      color: #ecf0f1;
      text-decoration: none;
      border-bottom: 1px solid #34495e;
      transition: 0.3s;
    }

    .sidebar ul.navbar li a:hover,
    .sidebar ul.navbar li a.active {
      background: #3498db;
      padding-left: 30px;
    }

    .content {
      margin-left: 260px;
      flex: 1;
      padding: 40px;
    }

    h2 {
      color: #2c3e50;
      border-bottom: 2px solid #3498db;
      padding-bottom: 10px;
      margin-top: 0;
    }

    /* --- Report Specific UI --- */
    .stats-grid {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      flex: 1;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      text-align: center;
      border-top: 4px solid #3498db;
    }

    .stat-card h3 {
      margin: 0;
      color: #7f8c8d;
      font-size: 14px;
      text-transform: uppercase;
    }

    .stat-card p {
      margin: 10px 0 0;
      font-size: 28px;
      font-weight: bold;
      color: #2c3e50;
    }

    .report-table-card {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table th,
    table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    table th {
      background: #f8f9fa;
      color: #2c3e50;
      font-weight: bold;
      font-size: 13px;
    }

    .prod-img-mini {
      width: 60px;
      height: 60px;
      border-radius: 4px;
      object-fit: cover;
      border: 1px solid #ddd;
    }

    .revenue {
      color: #27ae60;
      font-weight: bold;
      font-size: 16px;
    }

    .total-row {
      background: #f1f9ff;
      font-weight: bold;
    }

    /* Print Styles */
    @media print {
      .sidebar {
        display: none;
      }

      .content {
        margin-left: 0;
        padding: 0;
      }

      .btn-print {
        display: none;
      }
    }

    .btn-print {
      background: #34495e;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      float: right;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="logo">Premium Living<small>Staff Portal</small></div>
    <ul class="navbar">
      <li><a href="staff_index.php">📊 Dashboard</a></li>
      <li><a href="manage_orders.php">📦 Manage Orders</a></li>
      <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
      <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
      <li><a href="sales_report.php" class="active">📈 Sales Report</a></li>
    </ul>
  </div>

  <div class="content">
    <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
    <h2>Sales Statistics Report</h2>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Revenue</h3>
        <p>$<?php echo number_format($total_revenue_amount, 2); ?></p>
      </div>
      <div class="stat-card">
        <h3>Items Sold</h3>
        <p><?php echo $total_units_sold; ?> Units</p>
      </div>
      <div class="stat-card" style="border-top-color: #2ecc71;">
        <h3>Top Product</h3>
        <p><?php echo htmlspecialchars($top_product_name); ?></p>
      </div>
    </div>

    <div class="report-table-card">
      <h4 style="margin-top:0; color:#34495e;">Product Sales Breakdown</h4>
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Furniture Image</th>
            <th>Furniture Name</th>
            <th>Total Sold (Qty)</th>
            <th>Total Sales Amount ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $table_total_units = 0;
          $table_total_revenue = 0.00;

          if ($report_result && mysqli_num_rows($report_result) > 0) {
            while ($row = mysqli_fetch_assoc($report_result)) {
              // Accumulate totals for the table footer summary mapping
              $table_total_units += $row['oqty'];
              $table_total_revenue += $row['item_revenue'];

              // Handle conditional check for matching image assets
              $image_path = "../Sample Furniture Images/" . $row['fid'] . ".png";
              if (!file_exists($image_path)) {
                $image_path = "../Sample Furniture Images/1.png"; // Fallback placeholder
              }

              echo "<tr>";
              echo "<td><strong>#" . $row['oid'] . "</strong></td>";
              echo "<td><img src='" . $image_path . "' class='prod-img-mini' alt='Product Thumbnail'></td>";
              echo "<td><strong>" . htmlspecialchars($row['fname']) . "</strong><br><small style='color:#7f8c8d;'>Price: $" . number_format($row['fprice'], 2) . " each</small></td>";
              echo "<td>" . $row['oqty'] . "</td>";
              echo "<td class='revenue'>$" . number_format($row['item_revenue'], 2) . "</td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align:center; color:#7f8c8d; padding:30px;'>No completed sales records found in database matching report criteria.</td></tr>";
          }
          ?>
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td colspan="3" style="text-align: right;">GRAND TOTAL:</td>
            <td><?php echo $table_total_units; ?> Units</td>
            <td class="revenue" style="font-size: 20px;">$<?php echo number_format($table_total_revenue, 2); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

</body>

</html>