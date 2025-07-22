<?php
if (!empty($_POST['mail'])) {

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    include_once('classes/FormSqlBuilder.class.php');
    $formCreator = new SqlFormCreator($pdo);
    $formCreator->form_data = $_POST; //You can send any data. But array keys should match columns in your table
    $formCreator->remove_param = ["password"]; //Password wont be inserted raw. So we remove it at first
    $formCreator->extra_data = [['password', $password]]; //And we are adding password when we hash it.
    //We can add files etc as extra data too
    $formCreator->table_name = "users"; //Table that we are going to insert

    if ($formCreator->execute()) {

        $last_insert_id = $pdo->lastInsertId(); //Do your thing if you succeed

    } else {
        //Aaaah hell nooo!
        print_r($formCreator->createSql()); //You can check how cool your sql is if you want...
    }
}
?>


<form method="post">
    <div class="h-100 row">
        <div class="col-md-12">
            <div class="row mb-3">
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Name</label>
                    <input type="text" name="name" class="form-control mb-2" placeholder="Name" />
                </div>
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Phone</label>
                    <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Some Other Input</label>
                    <input type="text" name="other" class="form-control mb-2" placeholder="Some Other Input" />
                </div>
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Another One</label>
                    <input type="text" name="another" class="form-control mb-2" placeholder="Another One" />
                </div>
            </div>

            <div class="row mb-3">
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Some Date</label>
                    <input type="datetime-local" name="some_date" class="form-control" id="some_date">
                </div>
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Another Date</label>
                    <input type="datetime-local" name="another_date" class="form-control" id="another_date">
                </div>
            </div>
            <div class="row mb-3">
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">E-Mail </label>
                    <input type="text" name="mail" class="form-control mb-2" placeholder="E-Mail" />
                </div>
                <div class="mb-10 fv-row col-md-6">
                    <label class="required form-control-label">Password</label>
                    <input type="password" name="password" autocomplete="false" class="form-control mb-2" placeholder="Password" required />
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 tabs-border-top text-center py-5">
        <button type="submit" class="btn btn-success btn-md me-1"><i class="fa fa-check me-1"></i> Save</button>
    </div>
</form>
