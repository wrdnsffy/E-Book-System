<?php
session_start();

function initialize_cart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function add_to_cart($book_id, $quantity) {
    initialize_cart();
    
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] += $quantity;
    } else {
        $_SESSION['cart'][$book_id] = $quantity;
    }
}

function remove_from_cart($book_id) {
    if (isset($_SESSION['cart'][$book_id])) {
        unset($_SESSION['cart'][$book_id]);
    }
}

function update_cart_quantity($book_id, $quantity) {
    if (isset($_SESSION['cart'][$book_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$book_id] = $quantity;
        } else {
            remove_from_cart($book_id);
        }
    }
}

function get_cart_contents() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function get_cart_total_items() {
    return array_sum(get_cart_contents());
}
?>