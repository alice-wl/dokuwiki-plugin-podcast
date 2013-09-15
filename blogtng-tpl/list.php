<?php
  $dthlp =& plugin_load('helper', 'data');
  $sqlite = $dthlp->_getDB();
  $pcasthelper =& plugin_load("helper", "podcast");

  if(!$sqlite) return false;

  $page = $entry->entry['page'];
  $res = $sqlite->query( 'SELECT * FROM pages
    LEFT JOIN data AS T1 ON T1.pid = pages.pid
    WHERE page = ?', $page );
  $rows = $sqlite->res2arr($res);
  $cnt = count($rows);
  $p = array( );

  if( $cnt ) { 
    foreach( $rows as $i => $d ) {
      if( isset( $p[$d['T1.key']] )) {
        $p[$d['T1.key']].= ', '.$d['T1.value']; }
      else {
        $p[$d['T1.key']] = $d['T1.value']; }}        

  if( !$p['nr'] ) {
      $path = explode( ':', $page );
      $p['nr'] = array_pop( $path ); } 
  $url = $this->getConf( 'podcast_prefix' )
          .$p['nr'].".".$this->getConf( 'podcast_filetype' ); }

  $extensions = explode( ',', $this->getConf( 'podcast_extensions' ));
  if( $pcasthelper ) {
    $files = $pcasthelper->getfiles( $p['nr'], $this->getConf( 'podcast_prefix' ), $extensions ); }
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

    if( count( $files )) {
      echo "<div class='podcastaudio'>";
      echo "<audio controls>";
      foreach( $files as $ext => $f ) {
        echo "<source src='".$f['url']."' />"; }
      echo "</audio>";
      echo "<ul>";
      foreach( $files as $ext => $f ) {
        echo "<li><a href='".$f['url']."' />".$p['nr'].".$ext (".$f['hsize'].")</a></li>"; }
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
