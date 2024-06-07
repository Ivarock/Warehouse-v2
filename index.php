<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Warehouse\Models\User;
use Warehouse\Services\Warehouse;


$user = new User();


echo "1. Login\n";
echo "2. Register\n";
$choice = (int)readline("Enter your choice: ");

switch ($choice) {
    case 1:
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        if ($user->authorize($username, $password) === false) {
            echo "\nInvalid username or password. Exiting.\n";
            exit;
        }
        break;
    case 2:
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        if ($user->authorize($username, $password) === true) {
            echo "\nUser already exists. Exiting.\n";
            exit;
        }
        $user->register($username, $password);
        echo "\nRegistration successful. Please log in.\n";
        $username = readline("Enter your username: ");
        $password = readline("Enter your password: ");
        if ($user->authorize($username, $password) === false) {
            echo "\nInvalid username or password. Exiting.\n";
            exit;
        }
        break;
    default:
        echo "\nInvalid choice. Exiting.\n";
        exit;
}


$warehouse = new Warehouse($username);

function displayProducts(Warehouse $warehouse): void
{
    $output = new ConsoleOutput();
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Quantity', 'Price', 'Expiration date', 'Created at', 'Updated at']);
    foreach ($warehouse->getProducts() as $product) {
        $table->addRow([
            $product->getId(),
            $product->getName(),
            $product->getAmount(),
            $product->getPrice(),
            $product->getExpirationDate(),
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
    echo "6. Get product report\n";
    echo "7. Update product ID's\n";
    echo "8. Exit\n";
    $choice = (int)readline("Enter your choice: ");

    switch ($choice) {
        case 1:
            displayProducts($warehouse);
            break;
        case 2:
            $name = readline("Enter product name: ");
            $amount = (int)readline("Enter product amount: ");
            if ($amount <= 0) {
                echo "\nInvalid amount. Please enter a positive number.\n";
                break;
            }
            $price = (float)readline("Enter product price: ");
            if ($price <= 0) {
                echo "\nInvalid price. Please enter a positive number.\n";
                break;
            }
            $expirationDate = readline("Enter product expiration date (DD-MM-YYYY): ");
            $warehouse->createProduct($name, $amount, $price, $expirationDate);
            echo "\nProduct added successfully.\n";
            break;
        case 3:
            $id = readline("Enter product ID: ");
            $amount = (int)readline("Enter amount you want to add: ");
            if ($amount <= 0) {
                echo "\nInvalid amount. Please enter a positive number.\n";
                break;
            }
            $warehouse->addAmount($id, $amount);
            echo "\nProduct updated successfully.\n";
            break;
        case 4:
            $id = readline("Enter product ID: ");
            $amount = (int)readline("Enter amount you want to withdraw: ");
            if ($amount <= 0) {
                echo "\nInvalid amount. Please enter a positive number.\n";
                break;
            }
            if ($warehouse->getProduct($id)->getAmount() < $amount) {
                echo "\nAmount to remove exceeds current stock.\n";
                break;
            }
            $warehouse->withdrawAmount($id, $amount);
            echo "\nProduct updated successfully.\n";
            break;
        case 5:
            $id = readline("Enter product ID: ");
            $warehouse->deleteProduct($id);
            echo "\nProduct deleted successfully.\n";
            break;
        case 6:
            $report = $warehouse->getReport();
            echo "\nTotal products: {$report['totalProducts']}\n";
            echo "Total value: $ {$report['totalValue']}\n";
            break;
        case 7:
            $warehouse->updateProductId();
            echo "\nProduct ID's updated successfully.\n";
            break;
        case 8:
            echo "\nExiting...\n";
            exit;
        default:
            echo "\nInvalid choice.\n";
            break;
    }
}
