<?php

include __DIR__ . "/../vendor/autoload.php";

use \validity\FieldSet, \validity\Field;

$valid = true;
$sent = isset($_GET["sent"]);
$data = [];
if ($sent) {
    $langName = $_GET["language"] ?? "en";
    $langClass = ("ru" == $langName) ? \validity\language\Ru::class : \validity\language\En::class;
    $language = new $langClass();
$fieldSet = (new FieldSet($language))
    ->add(
        Field::pattern("name", "/^[A-Z][a-zA-Z\- ]+$/")->setRequired()
    )->add(
        Field::enum("greeting", ["mr", "mrs"])->setRequired()
    )->add(
        Field::int("subscriptions", "Invalid subscription selected ({key})")->setMin(1)->expectArray()
    )->add(
        Field::email("email")->setRequiredIf(
            function(FieldSet $fieldSet) {
                return (bool) $fieldSet->getFiltered("subscriptions");
            }
        )
    )->add(
        Field::date("date_of_birth")->setMax("-18years")->setRequired()
    )->add(
        Field::string("education")
            ->setMinLength(10, "{label} ({key}) is expected to have a minimum length of {min} characters")
            ->setMaxLength(100, "{label} ({key}) is expected to have a maximum length of {max} characters")
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
</head>
<body>
<div class="container">
    <?php include __DIR__ . "/menu.html"; ?>
    <div class="starter-template">
        <h1>Validity example</h1>
        <div class="row">
            <div class="col-sm-6">
                <form class="form" method="get">
                    <div class="form-group">
                        <label class="control-label requred">Error messages language</label>
                        <div>
                            <select name="language" class="form-control">
                                <option value="en">English</option>
                                <option value="ru" <?php if ("ru" == $langName) echo "selected"; ?>>Russian</option>
                            </select>
                        </div>
                    </div>
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
                <h3>Validation source code:</h3>
                <pre style='color:#000000;background:#ffffff;'><span style='color:#5f5035; background:#ffffe8; '>&lt;?php</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>new</span><span style='color:#000000; background:#ffffe8; '> FieldSet</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#797997; background:#ffffe8; '>$language</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>pattern</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"name"</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"</span><span style='color:#800000; background:#ffffe8; '>/</span><span style='color:#808030; background:#ffffe8; '>^</span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#0000e6; background:#ffffe8; '>A</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#0000e6; background:#ffffe8; '>Z</span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#0000e6; background:#ffffe8; '>a</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#0000e6; background:#ffffe8; '>zA</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#0000e6; background:#ffffe8; '>Z</span><span style='color:#0f69ff; background:#ffffe8; '>\-</span><span style='color:#0000e6; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>+</span><span style='color:#808030; background:#ffffe8; '>$</span><span style='color:#800000; background:#ffffe8; '>/</span><span style='color:#0000e6; background:#ffffe8; '>"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setRequired</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>enum</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"greeting"</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>[</span><span style='color:#0000e6; background:#ffffe8; '>"mr"</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"mrs"</span><span style='color:#808030; background:#ffffe8; '>]</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setRequired</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>int</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"subscriptions"</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"Invalid subscription selected ({key})"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMin</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>1</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>expectArray</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>email</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"email"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setRequiredIf</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>function</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '>FieldSet </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#800080; background:#ffffe8; '>{</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>return</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#bb7977; background:#ffffe8; '>bool</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>getFiltered</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"subscriptions"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#800080; background:#ffffe8; '>}</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#400000; background:#ffffe8; '>date</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"date_of_birth"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMax</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"-18years"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setRequired</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>string</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"education"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMinLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>10</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"{label} ({key}) is expected to have a minimum length of {min} characters"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMaxLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>100</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"{label} ({key}) is expected to have a maximum length of {max} characters"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>expectArray</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setArrayMinLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>0</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setArrayMaxLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>3</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setArraySkipEmpty</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0f4d75; background:#ffffe8; '>true</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>bool</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"xmas_wish"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>expectArray</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setArrayMinLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>0</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setArrayMaxLength</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>2</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#797997; background:#ffffe8; '>$valid</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>isValid</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#797997; background:#ffffe8; '>$_GET</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#5f5035; background:#ffffe8; '>?></span>
</pre>
            </div>
            <div class="col-sm-6">
                <?php include __DIR__ . "errors.php"; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>