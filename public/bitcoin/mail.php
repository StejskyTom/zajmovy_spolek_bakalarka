<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Overni metody POST a odeseni meilu
if (isset($_POST['send'])) {
    if (isset($_POST['mail'])) {
        $to = $_POST['mail'];
        $subject = 'Thank you';
        $message = 'Thanks for your donation';
        //mail($to, $subject, $message);
        if(!mail($to, $subject, $message)) {
            error_log("Не удалось отправить письмо на адрес $to");
        }
        // Presmerovani na hlavni stranku
        header('Location: index.php');
    }
}