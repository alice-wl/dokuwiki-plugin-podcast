<div class="blogtng_comment blogtng_comment_status_<?php $comment->tpl_status(); ?>" id="comment_<?php $comment->tpl_cid(); ?>">
    <div>
        <img src="<?php $comment->tpl_avatar(48, 48); ?>" class="avatar" width="48" height="48" alt="" align="left" />
        <div><?php $comment->tpl_hcard(); ?> No. <?php $comment->tpl_number(false); ?> @ <?php $comment->tpl_created(); ?></div>
        <br />
    </div>
    <?php $comment->tpl_comment(); ?>
    <div class="replytocomment">
        <?php $comment->tpl_number(true, "» Reply"); ?>
    </div>
</div>
