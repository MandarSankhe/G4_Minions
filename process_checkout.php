<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('dbinit.php');
    include('Cart.php');

    if (!isset($_SESSION['userid'])) {
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['userid'];
    $cart = new Cart($dbc, $userId);

    $cartItems = $cart->getCartItems();
    if (empty($cartItems)) {
        redirectWithError("Your cart is empty. Please add items to proceed.");
    }

    $subtotal = $cart->getTotalPrice();
    $taxRate = 0.10;
    $taxAmount = $subtotal * $taxRate;
    $finalTotal = $subtotal + $taxAmount;

    $inputs = sanitizeInputs($_POST);
    $errors = validateInputs($inputs);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: checkout_page.php");
        exit();
    }

    $dbc->begin_transaction();
    try {
        $addressId = insertAddress($dbc, $userId, $inputs);
        $orderId = insertOrder($dbc, $userId, $inputs, $finalTotal, $addressId);
        insertOrderDetails($dbc, $orderId, $cartItems);

        clearCart($dbc, $userId);
        $dbc->commit();

        header("Location: thank_you.php");
        exit();
    } catch (Exception $e) {
        $dbc->rollback();
        redirectWithError("An error occurred while processing your order. Please try again.");
    }
}

function sanitizeInputs($data) {
    return [
        'firstName' => htmlspecialchars(trim($data['firstName'])),
        'lastName' => htmlspecialchars(trim($data['lastName'])),
        'email' => filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL),
        'phone' => htmlspecialchars(trim($data['phone'])),
        'streetAddress' => htmlspecialchars(trim($data['street_address'])),
        'city' => htmlspecialchars(trim($data['city'])),
        'state' => htmlspecialchars(trim($data['state'])),
        'postalCode' => htmlspecialchars(trim($data['postal_code'])),
        'country' => htmlspecialchars(trim($data['country'])),
    ];
}

function validateInputs($inputs) {
    $errors = [];
    if (empty($inputs['firstName']) || !preg_match('/^[a-zA-Z\s]{2,}$/', $inputs['firstName'])) {
        $errors['firstName'] = "First Name is required and must be at least 2 characters long, containing only letters and spaces.";
    }
    if (empty($inputs['lastName']) || !preg_match('/^[a-zA-Z\s]{2,}$/', $inputs['lastName'])) {
        $errors['lastName'] = "Last Name is required and must be at least 2 characters long, containing only letters and spaces.";
    }
    if (empty($inputs['email']) || !filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }
    if (empty($inputs['phone']) || !preg_match('/^\d{10}$/', $inputs['phone'])) {
        $errors['phone'] = "Phone number must be exactly 10 digits and contain no special characters.";
    }
    if (empty($inputs['streetAddress']) || empty($inputs['city']) || empty($inputs['state']) || empty($inputs['postalCode']) || empty($inputs['country'])) {
        $errors['address'] = "Complete shipping information is required.";
    }
    if (!empty($inputs['postalCode']) && !preg_match('/^\d{5,10}$/', $inputs['postalCode'])) {
        $errors['postalCode'] = "Postal Code must be between 5 and 10 digits.";
    }

    if (empty($inputs['cardNumber']) || !preg_match('/^\d{16}$/', $inputs['cardNumber'])) {
        $errors['cardNumber'] = "Card number must be a valid 16-digit number.";
    }
    if (empty($inputs['expiryDate']) || !preg_match('/^\d{4}-\d{2}$/', $inputs['expiryDate'])) {
        $errors['expiryDate'] = "Expiry date is required and must be in YYYY-MM format.";
    }
    if (empty($inputs['cvv']) || !preg_match('/^\d{3}$/', $inputs['cvv'])) {
        $errors['cvv'] = "CVV must be a valid 3-digit number.";
    }


    return $errors;
}

function insertAddress($dbc, $userId, $inputs) {
    $query = "
        INSERT INTO addresses (user_id, type, street_address, city, state, postal_code, country) 
        VALUES (?, 'shipping', ?, ?, ?, ?, ?)";
    $stmt = $dbc->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed for insertAddress: " . $dbc->error);
    $stmt->bind_param(
        "isssss",
        $userId,
        $inputs['streetAddress'],
        $inputs['city'],
        $inputs['state'],
        $inputs['postalCode'],
        $inputs['country']
    );
    if (!$stmt->execute()) throw new Exception("Execute failed for insertAddress: " . $stmt->error);
    return $dbc->insert_id;
}

function insertOrder($dbc, $userId, $inputs, $total, $addressId) {
    $query = "
        INSERT INTO `order` 
        (userid, date, total, first_name, last_name, shipping_address_id) 
        VALUES (?, NOW(), ?, ?, ?, ?, ?)";
    $stmt = $dbc->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed for insertOrder: " . $dbc->error);
    $stmt->bind_param(
        "idsssi",
        $userId,
        $total,
        $inputs['firstName'],
        $inputs['lastName'],
        $addressId
    );
    if (!$stmt->execute()) throw new Exception("Execute failed for insertOrder: " . $stmt->error);
    return $dbc->insert_id;
}

function insertOrderDetails($dbc, $orderId, $cartItems) {
    $query = "INSERT INTO orderDetail (OrderID, productID, quantity) VALUES (?, ?, ?)";
    $stmt = $dbc->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed for insertOrderDetails: " . $dbc->error);

    foreach ($cartItems as $item) {
        $stmt->bind_param("iii", $orderId, $item['ID'], $item['quantity']);
        if (!$stmt->execute()) throw new Exception("Execute failed for insertOrderDetails: " . $stmt->error);
    }
}

function clearCart($dbc, $userId) {
    $query = "DELETE FROM cart WHERE userid = ?";
    $stmt = $dbc->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed for clearCart: " . $dbc->error);
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) throw new Exception("Execute failed for clearCart: " . $stmt->error);
}

function redirectWithError($message) {
    $_SESSION['errors'][] = $message;
    header("Location: checkout_page.php");
    exit();
}
