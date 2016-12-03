<?php

include __DIR__ . "/../vendor/autoload.php";

use \validity\FieldSet, \validity\Field;

$valid = true;
$sent = isset($_GET["sent"]);
$data = [];
if ($sent) {
    $fieldSet = (new FieldSet())
        ->add(
            Field::pattern("name", "/^[A-Z][a-zA-Z\- ]+$/", "Name: only latin letters and spaces, starting with a capital letter")
                ->setRequired()
        )->add(
            Field::enum("greeting", ["mr", "mrs"])->setRequired()
        )->add(
            Field::int("subscriptions", "Subscription ID is not an integer")->setMin(1)->expectArray()
        )->add(
            Field::email("email")->setRequiredIf(
                function($value, \validity\Report $report) {
                    return (bool) $report->getFiltered("subscriptions");
                },
                "Email is required in case you want to subscribe for news"
            )
        )->add(
            Field::date("date_of_birth")->setMax("-18years")->setRequired()
        )->add(
            Field::string("education")
                ->setMinLength(10)
                ->setMaxLength(100)
                ->expectArray()
                ->setArrayMinLength(0)
                ->setArrayMaxLength(3)
                ->setArraySkipEmpty(true)
        )->add(
            Field::bool("xmas_wish")
                ->expectArray()
                ->setArrayMinLength(0)
                ->setArrayMaxLength(2)
        );

    $valid = $fieldSet->isValid($_GET);
    $data = $fieldSet->getMixed();
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
    <style>
        label.required:before {
            content: "* ";
            color: red;
        }
    </style>
</head>
<body>
    <div style="margin: 1em auto; width: 90%; max-width: 80em;" class="container panel panel-info">
        <h1>Validity example</h1>
        <div class="row">
            <div class="col-sm-4">
                <form class="form" method="get">
                    <div class="form-group">
                        <label class="control-label required">Name (pattern /^[A-Z][a-zA-Z\- ]+$/)</label>
                        <div>
                            <input name="name" class="form-control" value="<?=$data["name"] ?? ""; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label requred">Greeting (enum [ mr, mrs ])</label>
                        <div>
                            <select name="greeting" class="form-control">
                                <?php $selected = $data["greeting"] ?? ""; ?>
                                <option value="" <?php if ("" == $selected) echo "selected"; ?>>Empty option for illustration</option>
                                <option value="mr" <?php if ("mr" == $selected) echo "selected"; ?>>Mr. </option>
                                <option value="mrs" <?php if ("mrs" == $selected) echo "selected"; ?>>Mrs. </option>
                                <option value="invalid" <?php if ("invalid" == $selected) echo "selected"; ?>>Invalid option for illustration</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label requred">Email subsriptions (array of integers)</label>
                        <?php $subscriptions = $data["subscriptions"] ?? []; ?>
                        <div class="checkbox">
                            <label><input type="checkbox" name="subscriptions[]"
                                          value="1" <?php if (in_array(1, $subscriptions)) echo "checked"; ?>>Essential news</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="subscriptions[]"
                                          value="2" <?php if (in_array(2, $subscriptions)) echo "checked"; ?>>Occasional SPAM</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="subscriptions[]"
                                          value="invalid" <?php if (in_array("invalid", $subscriptions)) echo "checked"; ?>>Invalid value</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Email (email pattern + required in case a subscription is selected)</label>
                        <div>
                            <input name="email" class="form-control" value="<?=$data["email"] ?? ""; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">Date of birth (date, maximum 18 years ago)</label>
                        <div>
                            <input name="date_of_birth" class="form-control" value="<?=$data["date_of_birth"] ?? ""; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">Education (array of string, 0 - 3 elements)</label>
                        <div>
                            <input name="education[]" class="form-control"
                                   value="<?=$data["education"][0] ?? ""; ?>"
                                   placeholder="School #1">
                        </div>
                        <div style="margin-top: .5em;">
                            <input name="education[]" class="form-control"
                                   value="<?=$data["education"][1] ?? ""; ?>"
                                   placeholder="School #2">
                        </div>
                        <div style="margin-top: .5em;">
                            <input name="education[]" class="form-control"
                                   value="<?=$data["education"][2] ?? ""; ?>"
                                   placeholder="School #3">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label requred">Christmas wishes, select maximum 2 (array of booleans, with string keys)</label>
                        <?php $xmas = $data["xmas_wish"] ?? []; ?>
                        <div class="checkbox">
                            <label><input type="checkbox" name="xmas_wish[xbox]"
                                          value="1" <?php if (isset($xmas["xbox"])) echo "checked"; ?>>X-box</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="xmas_wish[playstation]"
                                          value="1" <?php if (isset($xmas["playstation"])) echo "checked"; ?>>Playstation</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="xmas_wish[lego_mindstorm]"
                                          value="1" <?php if (isset($xmas["lego_mindstorm"])) echo "checked"; ?>>Lego mindstorm</label>
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
                            <p>A more detailed report:</p>
                            <pre class="alert alert-danger"
                                 style="white-space: pre-wrap"><?=htmlspecialchars(json_encode($fieldSet->getErrors()->export()))?></pre>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>