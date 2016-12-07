<?php

include __DIR__ . "/../vendor/autoload.php";

use \validity\FieldSet, \validity\Field;

$valid = true;
$sent = isset($_GET["sent"]);
$data = [];
if ($sent) {
    $fieldSet = (new FieldSet())
        ->add(
            Field::int("price_min")
                ->setMin(0)
                ->setMax(1000)
        )->add(
            Field::int("price_max")
                ->setMin(0)
                ->setMax(1000)
                ->addCallbackRule(function($value, FieldSet $fieldSet) {
                    return $value >= $fieldSet->getFiltered("price_min");
                }, "Max price cannot be lower than the min price")
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
        <h1>Validity example: price range</h1>
        <div class="row">
            <div class="col-sm-8">
                <form class="form form-inline" method="get">
                    <div class="form-group">
                        <label class="control-label">Price from</label>
                        <input name="price_min" class="form-control" style="width: 6em;">
                        <label class="control-label">to</label>
                        <input name="price_max" class="form-control" style="width: 6em;">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" name="sent">Send</button>
                    </div>
                </form>
                <h3>Validation source code:</h3>
                <pre style='color:#000000;background:#ffffff;'><span style='color:#5f5035; background:#ffffe8; '>&lt;?php</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>new</span><span style='color:#000000; background:#ffffe8; '> FieldSet</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>int</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"price_min"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMin</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>0</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMax</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>1000</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#400000; background:#ffffe8; '>add</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;Field</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#800080; background:#ffffe8; '>:</span><span style='color:#000000; background:#ffffe8; '>int</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"price_max"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMin</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>0</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>setMax</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#008c00; background:#ffffe8; '>1000</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>addCallbackRule</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>function</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#797997; background:#ffffe8; '>$value</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> FieldSet </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#800080; background:#ffffe8; '>{</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#800000; background:#ffffe8; font-weight:bold; '>return</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$value</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>getFiltered</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#0000e6; background:#ffffe8; '>"price_min"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#800080; background:#ffffe8; '>}</span><span style='color:#808030; background:#ffffe8; '>,</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#0000e6; background:#ffffe8; '>"Max price cannot be lower than the min price"</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#000000; background:#ffffe8; '>&#xa0;&#xa0;&#xa0;&#xa0;</span><span style='color:#797997; background:#ffffe8; '>$valid</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#808030; background:#ffffe8; '>=</span><span style='color:#000000; background:#ffffe8; '> </span><span style='color:#797997; background:#ffffe8; '>$fieldSet</span><span style='color:#808030; background:#ffffe8; '>-</span><span style='color:#808030; background:#ffffe8; '>></span><span style='color:#000000; background:#ffffe8; '>isValid</span><span style='color:#808030; background:#ffffe8; '>(</span><span style='color:#797997; background:#ffffe8; '>$_GET</span><span style='color:#808030; background:#ffffe8; '>)</span><span style='color:#800080; background:#ffffe8; '>;</span><span style='color:#000000; background:#ffffe8; '></span>
<span style='color:#5f5035; background:#ffffe8; '>?></span>
</pre>
            </div>
            <div class="col-sm-4">
                <?php include __DIR__ . "/errors.php"; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>