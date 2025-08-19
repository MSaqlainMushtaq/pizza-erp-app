<?php

function getAllSales($conn) {
    $sql = "SELECT * FROM sales ORDER BY created_at DESC";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getSaleItems($conn, $sale_id) {
    $stmt = $conn->prepare("
        SELECT si.*, p.name 
        FROM sale_items si 
        LEFT JOIN products p ON si.product_id = p.id 
        WHERE si.sale_id = ?
    ");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getSaleDeals($conn, $sale_id) {
    $stmt = $conn->prepare("
        SELECT sd.*, d.name, sd.unit_price
        FROM sale_deals sd
        LEFT JOIN deals d ON sd.deal_id = d.id
        WHERE sd.sale_id = ?
    ");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $deals = [];

    while ($row = $res->fetch_assoc()) {
        if (empty($row['name'])) {
            $row['name'] = 'Deal (Archived)'; 
        }
        $deals[] = $row;
    }

    return $deals;
}

function addSale($conn, $order_type, $delivery_address, $payment_method, $phone, $items, $created_by) {
    if (!is_array($items) || count($items) === 0) {
        return ['success' => false, 'error' => 'No items in sale.'];
    }

    $conn->begin_transaction();
    try {
        $total = 0.0;
        foreach ($items as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['unit_price'];
            $total += $qty * $price;
        }

        $stmt = $conn->prepare("
            INSERT INTO sales (total_price, order_type, payment_method, delivery_address, phone_number, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("dssss", $total, $order_type, $payment_method, $delivery_address, $phone);
        if (!$stmt->execute()) throw new Exception("Sale insert failed");
        $sale_id = $conn->insert_id;

        $stmtItem = $conn->prepare("
            INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        $stmtDeal = $conn->prepare("
            INSERT INTO sale_deals (sale_id, deal_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['unit_price'];

            if ($item['product_id']) {
                $pid = (int)$item['product_id'];
                $stmtItem->bind_param("iiid", $sale_id, $pid, $qty, $price);
                $stmtItem->execute();
                deductIngredients($conn, $pid, $qty, $sale_id);
            } elseif ($item['deal_id']) {
                $deal_id = (int)$item['deal_id'];
                $stmtDeal->bind_param("iiid", $sale_id, $deal_id, $qty, $price);
                $stmtDeal->execute();

                foreach (getProductsInDeal($conn, $deal_id) as $p) {
                    $pid = $p['product_id'];
                    $multiplied_qty = $p['quantity'] * $qty;
                    deductIngredients($conn, $pid, $multiplied_qty, $sale_id);
                }
            }
        }

        // Invoice creation
        $today = date('Ymd');
        $resInv = $conn->query("SELECT COUNT(*) AS cnt FROM invoices WHERE DATE(issued_at) = CURDATE() FOR UPDATE");
        $rowInv = $resInv->fetch_assoc();
        $seq = ((int)$rowInv['cnt']) + 1;
        $invoice_number = "INV-{$today}-" . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

        $stmtInv = $conn->prepare("
            INSERT INTO invoices (sale_id, invoice_number, issued_at, created_by)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmtInv->bind_param("isi", $sale_id, $invoice_number, $created_by);
        $stmtInv->execute();

        $conn->commit();
        return ['success' => true, 'sale_id' => $sale_id];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function deductIngredients($conn, $product_id, $quantity_sold, $sale_id) {
    $stmt = $conn->prepare("
        SELECT 
            r.ingredient_id,
            r.quantity_required,
            ing.cost_per_unit,
            ing.unit_id AS stock_unit_id,
            u_recipe.conversion_factor AS recipe_factor,
            u_stock.conversion_factor  AS stock_factor
        FROM recipes r
        JOIN ingredients ing ON ing.id = r.ingredient_id
        JOIN units u_recipe ON u_recipe.id = r.unit_id
        JOIN units u_stock ON u_stock.id = ing.unit_id
        WHERE r.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $ingredient_id = $row['ingredient_id'];
        $base_qty = $row['quantity_required'] * $quantity_sold;
        $stock_factor = (float)$row['stock_factor'];
        if ($stock_factor <= 0) continue;
        $stock_qty = $base_qty / $stock_factor;

        $stmtDeduct = $conn->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE id = ?");
        $stmtDeduct->bind_param("di", $stock_qty, $ingredient_id);
        $stmtDeduct->execute();

        $cost = $stock_qty * $row['cost_per_unit'];
        $stmtLog = $conn->prepare("
            INSERT INTO ingredient_usage_logs (ingredient_id, quantity_used, cost, used_at, sale_id)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmtLog->bind_param("idii", $ingredient_id, $stock_qty, $cost, $sale_id);
        $stmtLog->execute();
    }
}

function getProductsInDeal($conn, $deal_id) {
    $stmt = $conn->prepare("SELECT product_id, quantity FROM deal_items WHERE deal_id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getFilteredSales($conn, $day, $month, $year) {
    $stmt = $conn->prepare("
        SELECT * FROM sales 
        WHERE DAY(created_at) = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("iii", $day, $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function deleteSale($conn, $id) {
    $conn->begin_transaction();
    try {
        $items = getSaleItems($conn, $id);
        foreach ($items as $item) {
            restoreIngredients($conn, $item['product_id'], $item['quantity']);
        }
        $deals = getSaleDeals($conn, $id);
        foreach ($deals as $deal) {
            foreach (getProductsInDeal($conn, $deal['deal_id']) as $p) {
                restoreIngredients($conn, $p['product_id'], $p['quantity'] * $deal['quantity']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM ingredient_usage_logs WHERE sale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM invoices WHERE sale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM sale_items WHERE sale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM sale_deals WHERE sale_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function restoreIngredients($conn, $product_id, $quantity_sold) {
    $stmt = $conn->prepare("
        SELECT 
            r.ingredient_id,
            r.quantity_required,
            ing.unit_id AS stock_unit_id,
            u_stock.conversion_factor AS stock_factor
        FROM recipes r
        JOIN ingredients ing ON ing.id = r.ingredient_id
        JOIN units u_stock ON u_stock.id = ing.unit_id
        WHERE r.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $ingredient_id = $row['ingredient_id'];
        $base_qty = $row['quantity_required'] * $quantity_sold;
        $stock_factor = (float)$row['stock_factor'];
        if ($stock_factor <= 0) continue;
        $stock_qty = $base_qty / $stock_factor;

        $stmtRestore = $conn->prepare("UPDATE ingredients SET quantity = quantity + ? WHERE id = ?");
        $stmtRestore->bind_param("di", $stock_qty, $ingredient_id);
        $stmtRestore->execute();
    }
}
?>
