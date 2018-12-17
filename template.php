<?php
/**
 * Template Engine
 */

class Template {

  private $template;

  public function __construct($templatename) {
    $templatefile = __DIR__ . '/templates/' . $templatename . '.tpl';
    $this->template = file_get_contents($templatefile);
  }

  /**
   * replace placeholders in template with variables from $data
   * @param  array $data variable replacements
   * @return string      html code
   */
  public function render($data) {
    return $this->replacePattern($this->template, $data);
  }

  /**
   * Replace a pattern containing "%%key%%" token.
   * @param  string  $pattern    pattern
   * @param  array   $values     key value pairs for replacement
   * @param  string  $start      replacement marker start (default %%)
   * @param  string  $end        replacement marker end (default %%)
   * @param  boolean $encodeHtml strip html tags?
   * @return string replaced pattern
   */
  private function replacePattern($pattern, $values, $start = '%%', $end = '%%',
                                        $encodeHtml = false) {
    $search = array();
    $replace = array();
    foreach ($values as $key => $value) {
      $search[] = $start . $key . $end;
      $replace[] = $encodeHtml ? htmlentities($value) : (string) $value;
    }
    return str_replace($search, $replace, $pattern);
  }

}
