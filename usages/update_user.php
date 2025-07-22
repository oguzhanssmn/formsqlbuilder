<?php
$user_id = $_GET["id"]; //user id that you get as you wish
if (!empty($_POST['mail'])) {

    $password_status = [];
    if (!empty($_POST["password"])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_status = ['password', $password];
    }

    include_once('classes/FormSqlBuilder.class.php');
    $formCreator = new SqlFormCreator($pdo);
    $formCreator->form_data = $_POST;
    $formCreator->extra_data = [$password_status];
    $formCreator->table_name = "users";
    $formCreator->remove_param = ["sifre"];
    $formCreator->method = "UPDATE"; //Default method is insert but if you are going to
    //use update method you shoul define it like that
    $formCreator->update_condition = [
        ['id', $user_id],
        ['status', '!=', 'deleted'],
        ['age', '>', 18],
        ['created_at', '>=', '2024-01-01'],
        ['type', 'IN', ['admin', 'user']],
        ['tag', 'LIKE', '%php%']
    ]; //You have to add update condition like that. And for '=' you dont need to use operator

    if ($formCreator->execute()) {
        echo "Great success..";
    } else {
        echo "NOOOOOT XD";
        print_r($formCreator->createSql()); //You can check how cool your sql is if you want...
    }
}

?>


<form method="post">
    <!-- Your form is here but i cant see it -->
</form>