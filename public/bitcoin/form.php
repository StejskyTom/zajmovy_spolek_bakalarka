<?php
require "header.php";
//funkce validace
function validate_and_clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Overeni odesilani
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Объявление переменных и инициализация с пустыми значениями
    $email = validate_and_clean($_POST["mail"]);
    $amountUSD = validate_and_clean($_POST["amountUSD"]);

    // Overeni spravnosti meilu
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid mail format";
    }

    // Overeni castky
    if (!preg_match("/^[0-9]+$/", $amountUSD)) {
        $amountUSDErr = "Amount must be a number";
    }

    // Jestli chyby neni posleme uzivatele na stranku platby
    if (empty($emailErr) && empty($amountUSDErr)) {
        // Здесь может быть код для обработки платежа
        header("Location: payment.php");
        exit();
    }
}

?>
    <section class="container p-5">
        <h2 class="text-center">
            Enter your mail and amount of donate
        </h2>
        <form action="payment.php" method="post" class="d-flex flex-column justify-content-center w-25 mx-auto">
            <input type="email" name="mail" id="mail" required class="m-2" placeholder="E-mail" value="<?php echo $email; ?>">
            <span class="error"><?php echo $emailErr; ?></span>
            <input type="text" name="amountUSD" id="amountUSD" required class="m-2" placeholder="Amount in USD" pattern="[0-9]+$" value="<?php echo $amountUSD; ?>">
            <span class="error"><?php echo $amountUSDErr; ?></span>
            <button type="submit" class="m-2 btn btn-success"> Confirm </button>
        </form>
    </section>
<?php
require "footer.php";
?>