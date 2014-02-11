<?php
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
                  
  $page = $entry->entry['page'];
  $pcasthelper =& plugin_load("helper", "podcast");
  $files = array( );

  if( $pcasthelper ) {
    $p = $pcasthelper->get_info( $page );
    if( !$p['nr'] ) {
      $path = explode( ':', $page );
      $p['nr'] = array_pop( $path ); } 
    $files = $p['files']; }

?>
<div class="blogtng_entry">
    <div class="blogtng_postnavigation level1">
    <?php if ($link = $entry->tpl_previouslink('« @TITLE@', $entry->entry['page'], true)) { ?>
        <div class="blogtng_prevlink">
            <?php echo $link?>
        </div>
    <?php } ?>
    <?php if ($link = $entry->tpl_nextlink('@TITLE@ »', $entry->entry['page'], true)) { ?>
        <div class="blogtng_nextlink">
            <?php echo $link?>
        </div>
    <?php } ?>
    </div>
<?php
    //show headline
    echo "<h1 class=\"hspec\"><a href=\"";
    $entry->tpl_link();
    echo "\" class=\"postdate\">";
    $entry->tpl_created("%Y-%m-%d");
    echo "</a><a href=\"";
    $entry->tpl_link();
    echo "\"> // ".$entry->entry["title"]."</a></h1>";


    $source = array( );
    $links = array( );
    foreach( $files as $ext => $f ) {
      if( !$f['size'] ) continue;
      $source[] = "<source src='".$f['url']."' />";
      $links[]  = " <li><a href='".$f['url']."' />".$p['nr'].".$ext(".$f['hsize'].")</a></li>"; }

    echo "<div class='podcastaudio'>";
    if( count( $source )) {
      echo "<audio controls>";
      echo implode( "\n", $source );
      echo "</audio>"; }
    if( count( $links )) {
      echo "<ul>";
      echo implode( "\n", $links );
      echo "</ul>";
      echo "</div>"; }

    //show entry
    $entry->tpl_entry(//included
                      true,
                      //readmore (where to cut valid)
                      false,
                      //inc level
                      false,
                      //skip header
                      true); ?>
    <div class="clearer"></div>
    <div class="blogtng_footer">
        <div class="level1">
            <?php $entry->tpl_created("%Y-%m-%d @ %H:%M"); ?> |
            written by <?php $entry->tpl_author(); ?> |
            <?php if ($entry->has_tags()) { echo "Tags:"; $entry->tpl_tags(""); } ?>
        </div>
    </div>
    <a id="the__comments"></a>
    <?php
    if ($entry->entry["commentstatus"] !== "disabled") {
        //show existing comments
        if (!$entry->commenthelper){
            $entry->commenthelper =& plugin_load("helper", "blogtng_comments");
        }
        if ($entry->commenthelper->get_count() >= 1){
            echo "\n    <div class=\"level2\">\n        <h2 class=\"hspec\">".$entry->getLang("comments")."</h2>\n";
            $entry->tpl_comments($entry->entry["blog"]);
            echo "\n    </div>\n";
        }
        //show form to leave a comment
        if ($entry->entry["commentstatus"] !== "closed") {
            echo "\n    <div class=\"level2\">\n        <h2 class=\"hspec\">Leave a comment…</h2>\n";
            $entry->tpl_commentform();
            echo  "        <div id=\"commentform_notes\">\n"
                 ."            <ul id=\"commentform_notes_left\">\n"
                 ."                <li>E-Mail address will not be published.</li>\n"
                 ."                <li><strong>Formatting:</strong><br /><em>//italic//</em>&#160;&#160;<u>__underlined__</u><br /><strong>**bold**</strong>&#160;&#160;<code>''preformatted''</code></li>\n"
                 ."                <li><strong>Links:</strong><br />[[http://example.com]]<br />[[http://example.com|Link Text]]</li>\n"
                 ."                <li><strong>Quotation:</strong><br />&#62; This is a quote. Don't forget the space in front of the text: &#34;&#62; &#34;</li>\n"
                 ."            </ul>\n"
                 ."            <ul id=\"commentform_notes_right\">\n"
                 ."                <li><strong>Code:</strong><br />&lt;code&gt;This is unspecific source code&lt;/code&gt;<br />&lt;code [lang]&gt;This is specifc [lang] code&lt;/code&gt;<br />&lt;code php&gt;&lt;?php echo 'example'; ?&gt;&lt;/code&gt;<br />Available: html, css, javascript, bash, cpp, …</li>\n"
                 ."                <li><strong>Lists:</strong><br />Indent your text by two spaces and use a * for<br />each unordered list item or a - for ordered ones.</li>\n"
                 ."            </ul>\n"
                 ."            <div class=\"clear\"></div>\n"
                 ."        </div>\n"
                 ."        <div class=\"clear\"></div>\n";
            echo "\n    </div>\n";
        } else {
            echo  "\n    <div class=\"level2\">\n        <h3>Comments are closed</h3>\n"
                 ."\n    </div>\n";
        }
    } else {
        echo  "\n    <div class=\"level2\">\n        <h3>Comments are disabled</h3>\n"
             ."\n    </div>\n";
    }
    ?>
</div>
