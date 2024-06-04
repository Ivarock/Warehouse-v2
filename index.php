<?php

require 'vendor/autoload.php';

use Warehouse\Warehouse;
use Warehouse\User;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;


$user = new User();


echo "1. Login\n";
echo "2. Register\n";
$choice = (int)readline("Enter your choice: ");

switch ($choice) {
    case 1:
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        if ($user->authorize($username, $password) === false) {
            echo "Invalid username or password. Exiting.\n";
            exit;
        }
        break;
    case 2:
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        $user->register($username, $password);
        echo "Registration successful. Please log in.\n";
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        if ($user->authorize($username, $password) === false) {
            echo "Invalid username or password. Exiting.\n";
            exit;
        }
        break;
    default:
        echo "Invalid choice. Exiting.\n";
        exit;
}


$warehouse = new Warehouse($username);

function displayProducts(Warehouse $warehouse): void
{
    $output = new ConsoleOutput();
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Quantity', 'Created at', 'Updated at']);
    foreach ($warehouse->getProducts() as $product) {
        $table->addRow([
            $product->getId(),
            $product->getName(),
            $product->getAmount(),
            $product->getCreatedAt(),
            $product->getUpdatedAt(),
        ]);
    }
    $table->render();
}

while (true) {
    echo "\nWhat would you like to do?\n";
    echo "\n1. List all products\n";
    echo "2. Add new product to stock\n";
    echo "3. Add amount to existing product\n";
    echo "4. Withdraw amount from existing product\n";
    echo "5. Delete product from stock\n";
    echo "6. Update database status\n";
    echo "7. Exit\n";
    $choice = (int)readline("Enter your choice: ");

    switch ($choice) {
        case 1:
            displayProducts($warehouse);
            break;
        case 2:
            $id = (int)readline("Enter product ID: ");
            $name = readline("Enter product name: ");
            $amount = (int)readline("Enter product amount: ");
            $warehouse->addProduct($id, $name, $amount);
            echo "Product added successfully.\n";
            break;
        case 3:
            $id = (int)readline("Enter product ID: ");
            $amount = (int)readline("Enter amount you want to add: ");
            $warehouse->updateProduct($id, $amount, 'add');
            echo "Product updated successfully.\n";
            break;
        case 4:
            $id = (int)readline("Enter product ID: ");
            $amount = (int)readline("Enter amount you want to withdraw: ");
            $warehouse->updateProduct($id, $amount, 'withdraw');
            echo "Product updated successfully.\n";
            break;
        case 5:
            $id = (int)readline("Enter product ID: ");
            $warehouse->deleteProduct($id);
            echo "Product deleted successfully.\n";
            break;
        case 6:
            $status = readline("Enter new status: ");
            $warehouse->updateDatabase($status);
            echo "Database status updated successfully.\n";
            break;
        case 7:
            exit;
        default:
            echo "Invalid choice.\n";
            break;
    }
}
