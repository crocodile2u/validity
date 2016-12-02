<?php

include __DIR__ . "/../vendor/autoload.php";

use \validity\FieldSet, \validity\Field;

$valid = true;
$sent = isset($_GET["sent"]);
if ($sent) {
    $fieldSet = (new FieldSet())
        ->add(
            Field::pattern("name", "/[A-Z][a-zA-Z\- ]+/", "Name: only latin letters and spaces, starting with a capital letter")
                ->setRequired()
        );

    $valid = $fieldSet->isValid($_GET);
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Validity example</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
    <div style="margin: 1em auto; width: 90%; max-width: 80em;" class="container panel panel-info">
        <h1>Validity example</h1>
        <div class="row">
            <div class="col-sm-4">
                <form class="form" method="get">
                    <div class="form-group">
                        <label class="control-label requred">Name</label>
                        <div>
                            <input name="name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" name="sent">Send</button>
                    </div>
                </form>
            </div>
            <div class="col-sm-8">
                <div class="col-sm-12 well">
                    <p>Error summary will appear below</p>
                    <?php if ($sent) : ?>
                        <?php if ($valid) : ?>
                            <p>Data is valid</p>
                        <?php else : ?>
                            <pre class="alert alert-danger"
                                 style="white-space: pre-wrap"><?=htmlspecialchars($fieldSet->getErrors()->toString())?></pre>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>