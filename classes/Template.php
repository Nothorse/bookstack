<?php
namespace EBookLib;
/**
 * Template Engine
 */

class Template {

  /**
   * template
   * @var string
   */
  private $template;

  /**
   * get template file and contents.
   * @param string $templatename template name
   */
  public function __construct($templatename) {
    $templatefile = dirname(__DIR__) . '/templates/' . $templatename . '.tpl';
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
   * @param  string $pattern    pattern
   * @param  array  $values     key value pairs for replacement
   * @param  string $start      replacement marker start (default %%)
   * @param  string $end        replacement marker end (default %%)
   * @param  bool   $encodeHtml strip html tags?
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
