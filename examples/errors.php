<div class="col-sm-12 well">
    <?php if ($sent) : ?>
        <?php if ($valid) : ?>
            <p>Data is valid</p>
        <?php else : ?>
            <p>Error summary as string (FieldSet->getErrors()->toString())</p>
            <pre class="alert alert-danger"
                 style="white-space: pre-wrap"><?=htmlspecialchars($fieldSet->getErrors()->toString())?></pre>

            <p>Error summary as list (FieldSet->getErrors()->toPlainArray())</p>
            <ul class="alert alert-danger" style="list-style: none;">
                <?php foreach ($fieldSet->getErrors()->toPlainArray() as $field => $message) : ?>
                    <li><b><?=$field?></b>: <?=htmlspecialchars($message, ENT_QUOTES)?></li>
                <?php endforeach; ?>
            </ul>
            <p>Full error report (FieldSet->getErrors()->export())</p>
            <pre class="alert alert-danger"
                 style="white-space: pre-wrap"><?=htmlspecialchars(json_encode($fieldSet->getErrors()->export()), ENT_QUOTES)?></pre>
        <?php endif; ?>
    <?php endif; ?>
</div>