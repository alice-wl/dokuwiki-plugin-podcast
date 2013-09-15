<?php echo $entry->get_entrycontent() ?>

<p>
    <small>
        This blog post was created <?php $entry->tpl_created("on %Y-%m-%d at %H:%M")?> by
        <?php $entry->tpl_author()?>.
        <?php if ($entry->has_tags()):?>
        It is tagged with <?php $entry->tpl_tagstring("")?>.
        <?php endif ?>
    </small>
</p>
