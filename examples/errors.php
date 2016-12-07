<div class="col-sm-12 well">
    <?php if ($sent) : ?>
        <?php if ($valid) : ?>
            <p>Data is valid</p>
        <?php else : ?>
            <p>Error summary as string (<i>FieldSet->getErrors()->toString()</i>)</p>
            <pre class="alert alert-danger"
                 style="white-space: pre-wrap"><?=htmlspecialchars($fieldSet->getErrors()->toString())?></pre>

            <p>Error summary as list (<i>FieldSet->getErrors()->toPlainArray()</i> &mdash; neatly formatted as HTML &lt;ul&gt;)</p>
            <ul class="alert alert-danger" style="list-style: none;">
                <?php foreach ($fieldSet->getErrors()->toPlainArray() as $field => $message) : ?>
                    <li><b><?=$field?></b>: <?=htmlspecialchars($message, ENT_QUOTES)?></li>
                <?php endforeach; ?>
            </ul>
            <p>Full error report (<i>FieldSet->getErrors()->export()</i> &mdash; json_encode()'d)</p>
            <pre class="alert alert-danger"
                 style="white-space: pre-wrap"><?=htmlspecialchars(json_encode($fieldSet->getErrors()->export()), ENT_QUOTES)?></pre>
        <?php endif; ?>
    <?php endif; ?>
</div>