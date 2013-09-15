<?php
/* author: alice@muc.ccc.de
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_podcast_feed extends DokuWiki_Action_Plugin{
    var $entryhelper = null;
    var $pcasthelper = null;
    var $tools = null;
    var $defaultConf = array(
        'sortby' => 'created',
        'sortorder' => 'DESC',
    );
    function action_plugin_podcast_feed() {
        $this->entryhelper =& plugin_load('helper', 'blogtng_entry');
        $this->pcasthelper =& plugin_load('helper', 'podcast');
        $this->tools =& plugin_load('helper', 'blogtng_tools');
    }
    function register(&$controller) {
        $controller->register_hook('FEED_OPTS_POSTPROCESS', 'AFTER', $this, 'handle_opts_postprocess', array());
        $controller->register_hook('FEED_MODE_UNKNOWN', 'BEFORE', $this, 'handle_mode_unknown', array ());
        $controller->register_hook('FEED_ITEM_ADD', 'BEFORE', $this, 'handle_item_add', array());
    }
    function handle_opts_postprocess(&$event, $param) {
        $opt =& $event->data['opt'];
        if ($opt['feed_mode'] != 'podcast') return;
        $opt['blog'] = $_REQUEST['blog'];
        $opt['tags'] = $_REQUEST['tags'];
        $opt['sortby'] = $_REQUEST['sortby'];
        $opt['sortorder'] = $_REQUEST['sortorder'];
        $opt['feed_type'] = 'RSS2.0';
    }
    function handle_mode_unknown(&$event, $param) {
        $opt = $event->data['opt'];
        if ($opt['feed_mode'] !== 'podcast') return;
        $event->preventDefault();
        $event->data['data'] = array();
        $conf = array(
            'blog' => explode(',', $opt['blog']),
            'tags' => ($opt['tags'] ? explode(',', $opt['tags']) : null),
            'sortby' => $opt['sortby'],
            'sortorder' => $opt['sortorder'],
            'limit' => $opt['items'],
            'offset' => 0,
        );
        $this->tools->cleanConf($conf);
        $conf = array_merge($conf, $this->defaultConf);
        $posts = $this->entryhelper->get_posts($conf);
        if( $opt['feed_mode'] === 'podcast' ) {
            $dthlp =& plugin_load('helper', 'data');
            $sqlite = $dthlp->_getDB();
            if(!$sqlite) return false;
            // image -> url, title, link, widh, height, description
            // language
            // copyright
            // category (array)
            // rating
        }
        foreach ($posts as $row) {
            if( auth_quickaclcheck( $row['id'] ) < AUTH_READ ) {
                continue; }
            if( $opt['feed_mode'] === 'podcast' ) {
                $res = $sqlite->query( 'SELECT 
                    T1.key as key,
                    T1.value as value
                  FROM pages
                    LEFT JOIN data AS T1 ON T1.pid = pages.pid
                    WHERE page = ?', $row['page'] );
                $rows = $sqlite->res2arr($res);
                $cnt = count($rows);
                $p = array( );
                if( $cnt ) {  
                foreach( $rows as $i => $d ) {
                  if( isset( $p[$d['key']] )) { $p[$d['key']].= ', '.$d['value']; }
                  else { $p[$d['key']] = $d['value']; }}}
                if( !isset( $p['nr'] )) {
                    $path = explode( ':', $row['page'] );
                    $p['nr'] = array_pop( $path ); } 
                $row['data_entry'] = $p; }
            $event->data['data'][] = array(
                'id' => $row['page'],
                'date' => $row['created'],
                'user' => $row['author'],
                'entry' => $row );
        }
    }
    function handle_item_add(&$event, $param) {
        $opt = $event->data['opt'];
        if ($opt['feed_mode'] !== 'podcast') return;
        $opt['link_to'] = 'current';
        $opt['item_content'] = 'html';
        $ditem = $event->data['ditem'];
        $p = $event->data['ditem']['entry']['data_entry'];

        if( !$p['nr'] ) {
          $path = explode( ':', $ditem['id'] );
          $p['nr'] = array_pop( $path ); }

        // don't add drafts to the feed
        if(p_get_metadata($ditem['id'], 'type') == 'draft') {
            $event->preventDefault();
            return;
        }

        // retrieve first heading from page instructions
        $ins = p_cached_instructions(wikiFN($ditem['id']));
        $headers = array_filter($ins, array($this, '_filterHeaders'));
        $headingIns = array_shift($headers);
        $firstheading = $headingIns[1][0];

        $this->entryhelper->load_by_row($ditem['entry']);
        $page_url = wl($id, 's=feed', true, '&');
        $tag_url = wl( 'tags:', 's=feed', true, '&');
        $file_url = $this->getConf( 'podcast_prefix' )
                .$p['nr'].".".$this->getConf( 'podcast_filetype' );
        $length = $this->pcasthelper->get_headers_length( $file_url );
        $filetype = 'audio/mpeg';

        $output = '';
        ob_start();
        $this->entryhelper->tpl_content($ditem['entry']['blog'], 'feed');
        $output = ob_get_contents();
        ob_end_clean();
        // make URLs work when canonical is not set, regexp instead of rerendering!
        global $conf;
        if(!$conf['canonical']){
            $base = preg_quote(DOKU_REL,'/');
            $output = preg_replace('/(<a href|<img src)="('.$base.')/s','$1="'.DOKU_URL,$output);
        }
        // strip first heading and replace item title


        $event->data['item']->description = preg_replace('#[^\n]*?>\s*?' . preg_quote(hsc($firstheading), '#') . '\s*?<.*\n#', '', $output, 1);
        $event->data['item']->title = $ditem['entry']['title'];
        $event->data['item']->guid = $ditem['entry']['pid'];

        $event->data['guid'] = $file_url;
        $event->data['item']->enclosure = (object)array( 
                'url' => $file_url, 'length' => $length, 'type' => $filetype );

        if( $p['image'] ) { // kaputt
            $event->media = $p['image']; }

        $output = '';
        ob_start();
        $this->entryhelper->tpl_tags( $tag_url );
        $output = ob_get_contents();
        ob_end_clean();
    }

    /**
     * Returns true if $entry is a valid header instruction, false otherwise.
     *
     * @author Gina Häußge <osd@foosel.net>
     */
    function _filterHeaders($entry) {
        // normal headers
        if (is_array($entry) && $entry[0] == 'header' && count($entry) == 3 && is_array($entry[1]) && count($entry[1]) == 3)
            return true;

        // no known header
        return false;
    }
}
// vim:ts=4:sw=4:et:
