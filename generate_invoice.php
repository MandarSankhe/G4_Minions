<?php
ob_start(); 
require('./fpdf/fpdf.php');
include('dbinit.php'); // Database initialization file

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Image('public/Images/login-banner.png', 10, 10, 30);
        $this->SetTextColor(0, 0, 128);
        $this->Cell(0, 10, 'Invoice', 0, 1, 'C');
        $this->Ln(2);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Line(10, 25, 200, 25);
        $this->Ln(10);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Thank you for choosing Minions TVstore!', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-30);
        $this->SetDrawColor(255, 215, 0);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(0, 0, 128);
        $this->Cell(0, 10, '2024 Minions TVstore - All Rights Reserved', 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;

    if (!$orderId) {
        die("Order ID is required.");
    }

    // Fetch order and customer details
    $stmt = $dbc->prepare("
        SELECT o.OrderID, o.date, o.total, o.first_name, o.last_name, a.street_address, a.city, a.state, a.postal_code, a.country
        FROM `order` o
        JOIN users u ON o.userid = u.ID
        JOIN addresses a ON o.shipping_address_id = a.ID
        WHERE o.OrderID = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result();

    if ($orderResult->num_rows === 0) {
        die("Order not found.");
    }

    $orderData = $orderResult->fetch_assoc();

    // Fetch order items
    $stmt = $dbc->prepare("
        SELECT p.Model, p.Brand, od.quantity, p.Price
        FROM orderDetail od
        JOIN products p ON od.productID = p.ID
        WHERE od.OrderID = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();

    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }

    // Create PDF
    $pdf = new PDF();
    $pdf->AddPage();

    // Customer Details Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(230, 230, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, "Customer Details", 0, 1, 'L', true);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetDrawColor(200, 200, 200);

    $pdf->Cell(40, 10, "Order ID:", 1, 0, 'L', true);
    $pdf->Cell(150, 10, $orderData['OrderID'], 1, 1, 'L', true);

    $pdf->Cell(40, 10, "Name:", 1, 0, 'L', true);
    $pdf->Cell(150, 10, "{$orderData['first_name']} {$orderData['last_name']}", 1, 1, 'L', true);

    $pdf->Cell(40, 10, "Shipping Address:", 1, 0, 'L', true);
    $pdf->Cell(150, 10, "{$orderData['street_address']}, {$orderData['city']}, {$orderData['state']} - {$orderData['postal_code']}, {$orderData['country']}", 1, 1, 'L', true);

    $pdf->Cell(40, 10, "Date:", 1, 0, 'L', true);
    $pdf->Cell(150, 10, $orderData['date'], 1, 1, 'L', true);

    $pdf->Ln(10);

    // Order Items Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(70, 10, 'Model', 0, 0, 'C', true);
    $pdf->Cell(50, 10, 'Brand', 0, 0, 'C', true);
    $pdf->Cell(30, 10, 'Quantity', 0, 0, 'C', true);
    $pdf->Cell(40, 10, 'Price', 0, 1, 'C', true);

    $pdf->SetFont('Arial', '', 12);
    $rowSwitch = true;
    $rowColor1 = [240, 248, 255];
    $rowColor2 = [255, 255, 255];
    $totalPrice = 0;

    foreach ($items as $item) {
        $rowColor = $rowSwitch ? $rowColor1 : $rowColor2;
        $pdf->SetFillColor(...$rowColor);

        $pdf->Cell(70, 10, $item['Model'], 0, 0, 'L', true);
        $pdf->Cell(50, 10, $item['Brand'], 0, 0, 'L', true);
        $pdf->Cell(30, 10, $item['quantity'], 0, 0, 'C', true);
        $pdf->Cell(40, 10, '$' . number_format($item['Price'], 2), 0, 1, 'R', true);

        $totalPrice += $item['quantity'] * $item['Price'];
        $rowSwitch = !$rowSwitch;
    }

    $pdf->Ln(10);

    // Calculate Subtotal, Tax, and Final Total
    $subtotal = $totalPrice;
    $taxRate = 0.10; // 10% tax
    $taxAmount = $subtotal * $taxRate;
    $finalTotal = $subtotal + $taxAmount;

    // Totals Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 250);

    $pdf->Cell(120, 10, '', 0);
    $pdf->Cell(30, 10, 'Subtotal', 0, 0, 'C', true);
    $pdf->Cell(40, 10, '$' . number_format($subtotal, 2), 0, 1, 'R', true);

    $pdf->Cell(120, 10, '', 0);
    $pdf->Cell(30, 10, 'Tax (10%)', 0, 0, 'C', true);
    $pdf->Cell(40, 10, '$' . number_format($taxAmount, 2), 0, 1, 'R', true);

    $pdf->Cell(120, 10, '', 0);
    $pdf->Cell(30, 10, 'Total', 0, 0, 'C', true);
    $pdf->Cell(40, 10, '$' . number_format($finalTotal, 2), 0, 1, 'R', true);
    
    $pdf->SetTitle("ORDER-{$orderData['OrderID']}-{$orderData['first_name']}-{$orderData['last_name']}");

    $fileName = "ORDER-{$orderData['OrderID']}-{$orderData['first_name']}-{$orderData['last_name']}.pdf";

    ob_end_clean();

    $pdf->Output('I', $fileName);
}
?>
