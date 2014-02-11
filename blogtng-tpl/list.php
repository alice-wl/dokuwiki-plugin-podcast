<?php
  $pcasthelper =& plugin_load("helper", "podcast");

  $page = $entry->entry['page'];

  if( $pcasthelper ) {
    $p = $pcasthelper->get_info( $page );

    if( !$p['nr'] ) {
      $path = explode( ':', $page );
      $p['nr'] = array_pop( $path ); } 
    $files = $p['files']; }

?>
<div class="blogtng_list"><?php
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
                      //readmore (where to cut valid when using ~~READMORE~~)
                      "syntax",
                      //inc level
                      false,
                      //skip header
                      true);
?>
    <div class="clearer"></div>
    <div class="blogtng_footer">
        <div class="level1">
            <?php $entry->tpl_created("%Y-%m-%d"); ?>&#160;written&#160;by&#160;<?php $entry->tpl_author(); ?> |
            <a href="<?php $entry->tpl_link(); ?>" class="wikilink1 blogtng_permalink" title="<?php echo hsc($entry->entry["title"]); ?>">Permanentlink</a> |
            <?php
            if ($entry->has_tags()){
                echo "Tags:"; $entry->tpl_tags("");
            }
            ?>
        </div>
    </div>
</div>
