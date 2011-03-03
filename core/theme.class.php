<?php

/**
 * Theming system for the lemur framework.
 */
class Template {

   // Defines which general (page-level) TPL to use.
   public function setTPL($tpl) {
     $this->tpl = $tpl;
   }

   // Set TTL for the page.
   public function setTTL($ttl) {
     $this->ttl = $ttl;
   }

   // Set LMT (Last-Modified-Time) for the page.
   public function setLMT($lmt) {
     $this->lmt = $lmt;
   }

   // Set the set of templates that need to be used.
   public function setPath($tplpath) {
     $this->tplpath = $tplpath;
   }

   /**
    * Set an array with $vars for the page. This function allows
    * controllers to pass vars to TPLs in the page level.
    */
   public function setVars($vars) {
     $this->vars = array_merge($this->vars, $vars);
   }

   /**
    * Allow controllers to set error pages.
    */
   public function error($error) {
     ; // FIXME: Write this function.
   }

  /**
   * Just print content.
   * This is call only to render the page.tpl.php level.
   * The page.tpl.php level is defined in $this->tpl using setTPL().
   */
  public function parse($type, $data = FALSE, $special = FALSE) {
    global $t_tpl_start;
    $t_tpl_start = microtime(TRUE);

    $this->vars = array_merge($this->vars, self::_vars_common());

    if ($special === FALSE) {
      global $db;
      if (is_object($db)) {
        $db->close('master'); // Make sure no queries in theming to master.
        $db->close('slave');  // Make sure no queries in theming to slaves.
      }
    }

    extract($this->vars, EXTR_SKIP);
    if (is_array($data)) {
      extract($data, EXTR_SKIP);
    }
    ob_start();
    include self::getPath() . '/' . $type . '.tpl.php';
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  /**
   * Print all. Usually this is the last call.
   */
  public function render() {

    /**
     * Process TTLs.
     */
    if ($this->ttl !== array()) {
      if (isset($this->ttl['user'])) {

        // 'user' TTL for user browser.
        if ($this->ttl['user'] == 'nostore') {
          header('Expires: Tue, 26 Oct 1982 08:30:00 GMT');
          header('Cache-Control: max-age=0, no-cache, no-store');
        }
        else {
          header('Expires: '. gmdate('D, d M Y H:i:s', time()+$this->ttl['user']) .' GMT');
          header('Cache-Control: max-age=' . $this->ttl['user'] . ', public');
        }

        // 'varn' TTL for varnish.
	if ($this->ttl['varn'] == 'nostore') {
          // FIXME: nostore and 0s is not the same!.
	  header('Primary-Control: 0s');
	}
	else {
	  header('Primary-Control: '. $this->ttl['varn']);
	}

        // 'edge' TTL for Akamai Edge servers.
	if ($this->ttl['edge'] == 'nostore') {
          header('Edge-Control: max-age=0,no-cache,no-store');
	}
	else {
          header('Edge-Control: cache-maxage=' . $this->ttl['edge']);
	}
      }
    }

    /**
     * Process LMT (Last-Modified-Time).
     */
    if ($this->lmt != '') {
      header('Last-Modified: '. gmdate('D, d M Y H:i:s', $this->lmt) .' GMT');
    }
    else {
      header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
    }

    /**
     * And then process regions, blocks and parse the TPL.
     */
    $this->vars = array_merge($this->vars, self::_vars_common());
    foreach ($this->regions as $region => $active) {
      $this->vars[$region] = $this->_parse_blocks($region);
    }
    return $this->parse($this->tpl);
  }

  /**
   * Add blocks with content to regions in the theming object. Controllers
   * use that function to define which blocks go in each URL.
   */
  public function add($region, $type, $data = array(), $weight = 0) {
    $this->regions[$region]         = 1; // Declares the region.
    $this->blocks[$region][][$type] = $data;
  }

  /**
   * Cuts a string to a defined size based on parameters. It removes a few
   * arguments between '-' if the string is longer enough. It also cut the
   * string if it is still too long (adding the characters that informs the
   * user the string has been cut).
   */
  public static function stringCut($string, $size_limit, $add, $remove_n_words) {
    mb_internal_encoding('UTF-8');

    // Human to machine lenght.
    if ($remove_n_words > 0) {
      $remove_n_words++;
    }
    $num_words = count(explode(' - ', $string));

    /**
     * First remove words in the beginning.
     */
    if ($num_words <= $remove_n_words) {
      // The string is short enough.
      $new_string = $string;
    }
    else {
      /**
       * Cut is needed.
       *
       * The 'explode' function with the limit makes the magic:
       *
       * $string_array = array(
       *   0   => 'first arg',
       *   ... => 'following arg...',
       *   $remove_n_words-1 => 'last argument to remove',
       *   $remove_n_words   => 'rest of the string',
       * );
       *
       * And then the 'trim' removes spaces and '-' at the beginning
       * and the end of the string.
       */
      $string_array = explode(' - ', $string, $remove_n_words);
      $new_string   = trim(end($string_array), ' -');
    }

    /**
     * Then cut the string to $size_limit characters.
     */
    if (mb_strlen($new_string) > $size_limit) {
      $add_size = mb_strlen($add);
      return mb_substr($new_string, 0, ($size_limit-$add_size)) . $add;
    }
    else {
      return $new_string;
    }
  }

  /**************************************************************************
   * Private functions.
   **************************************************************************/

   /**
    * Get configured path for TPLs.
    */
   private function getPath() {
     global $path_theme;

     if ($this->tplpath != '') {
       return $this->tplpath;
     }
     if (isset($path_theme) && $path_theme != '') {
       return $path_theme;
     }
     return 'themes/default';
   }

  /**
   * Parse blocks in regions.
   */
  private function _parse_blocks($region) {
    $output = '';

    // No blocks found in this region.
    if (!is_array($this->blocks) || !is_array($this->blocks[$region])) {
      return $output;
    }

    // Print block by block, in order.
    foreach ($this->blocks[$region] as $blocks) {
      foreach ($blocks as $blockname => $block) {
        extract($this->vars, EXTR_OVERWRITE);
        if (is_array($block)) {
          extract($block, EXTR_OVERWRITE);
        }
        ob_start();
        include self::getPath() . "/$blockname.tpl.php";
        $output .= ob_get_contents();
        ob_end_clean();
      }
    }

    return $output;
  }

  /**
   * Theming system common vars for all templates and blocks.
   */
  private function _vars_common() {
    global $base_url;
    global $url;
    global $vars;

    $vars_common = array(
      'base_url'      => $base_url,
      'url'           => $url,
      'path_to_theme' => self::getPath(),
    );

    if (is_array($vars)) {
      return array_merge($vars_common, $vars);
    }
    return $vars_common;
  }

  private $blocks  = array();
  private $regions = array();
  private $vars    = array();
  private $ttl     = array();
  private $tpl     = '';
  private $tplpath = '';
  private $lmt     = '';
}
