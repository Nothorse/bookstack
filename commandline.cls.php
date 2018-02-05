<?php
require __DIR__ . '/lib/vendor/autoload.php';
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

/**
 * Complete command line base class.
 * Takes care of parameter setup, --help output
 * and IO.
 * Needs to be subclassed for the specific script.
 * The script also needs two lines outside the class definition
 * to instantiate the class and call the main method.
 * EXAMPLE:
 *   $script = new MiracleScript();
 *   $script->main();
 *
 * !!: All parameters must be named.
 * !!: Unrecognized strings after the
 * !!: parameters are discarded.
 *
 * @author    Thomas Hassan <t.hassan@intevo.net>
 * @package   framework
 * @copyright intevo.websolutions gmbh, 2010
 * @version   $Id$
 */
abstract class CommandLine {

  /**
   * Parameters and helptext
   * ([parameter] => ([varname] => [helptext]))
   *
   * @var array
   */
  private $params;
  private $structparams;

  /**
   * Application::environment
   *
   * @var object
   */
  protected $env;

  /**
   * Application::database
   *
   * @var object
   */
  protected $db;

  /**
   * commandline arguments
   * ([varname] => [value])
   *
   * @var string
   */
  protected $args;

  /**
   * built helpstring
   * usage + \n + param + helptext
   *
   * @var string
   */
  protected $helpstring;

  /**
   * custom usage string
   * normally built automagically
   *
   * @var string
   */
  private $usage;

  /**
   * Command Name -- set for ease of building usage
   *
   * @var string
   */
  private $cmdname;

  /**
   * Constructor
   * sets params, gets the parameters from the commandline,
   * handles --help output
   *
   */
  public function __construct() {
    $this->addParam(array('q'), 'QUIET', "No output. Scripts that demand interactive user input cannot be run this way");
    $this->addParam(array('h','help'), 'HELP', "Display this help");
    $this->initParams();
    $shortopts = '';
    $longopts = array();
    $parlen = 1;
    foreach($this->params as $flag => $param) {
      $flags = array();
      // Remove : and = from flag for help and doc purposes
      $cleanflag = str_replace(array(':','='), '', $flag);
      $parlen = (strlen($cleanflag) > $parlen) ? strlen($cleanflag) : $parlen;
      if (strpos($flag, ',') > 0) {
        $flags = explode(',', $flag);
      } else {
        $flags = array($flag);
      }
      foreach($flags as $i => $singleflag) {
        $this->args[$param['varname']] = false;
        if (substr($singleflag, 0, 2) == '--') {
          $longopts[] = str_replace('-', '', $singleflag);
        } else {
          $shortopts .= str_replace('-', '', $singleflag);
        }
      }
    }
    $this->helpstring = (strlen($this->usage) > 0) ? $this->usage : "Usage: tbx [-q] ".$this->cmdname." [options]\n";
    $helplen = 80 - ($parlen +2);
    foreach($this->params as $flag => $param) {
      if ($param['help'] === true) {
        continue;
      }
      $ptext = str_pad(str_replace(array(':','='), '', $flag), $parlen + 2, ' ');
      $htext = wordwrap($param['help'], $helplen, "\n");
      if (strpos($htext, "\n") !== false) {
        $helparray = explode("\n", $htext);
        foreach($helparray as $index => $htext) {
          $this->helpstring .= ($index == 0) ? $ptext . $htext . "\n" : str_repeat(' ', $parlen + 2) . $htext . "\n";
        }
      } else {
          $this->helpstring .=  $ptext . $htext . "\n";
      }
    }
    $args = $this->getArgs();
    foreach($this->params as $flag => $param) {
      $flags = array();
      // Remove : and = from flag for help and doc purposes
      $cleanflag = str_replace(array(':','='), '', $flag);
      if (strpos($cleanflag, ',') > 0) {
        $flags = explode(',', $cleanflag);
      } else {
        $flags = array($cleanflag);
      }
      foreach($flags as $i => $singleflag) {
        $singleflag = (strlen($singleflag) > 2) ? $singleflag : str_replace('-', '', $singleflag);
        $varname = $param['varname'];
        if(isset($args[$singleflag]) && !$this->args[$varname]) {
          $this->args[$varname] = $args[$singleflag];
        }
      }
    }
    if ($this->getArgument('HELP')) {
      $this->showHelp();
      exit;
    }
  }

  /**
   * Neds to be implemented in subclass to define
   * commandline parameters
   *
   * @return void
   */
  abstract protected function initParams();

  /**
   * Needs to be implemented in subclass.
   * Main handler, runs the script.
   *
   * @return void
   */
  abstract public function main();

  /**
   * addParam -- Adds the parameter definitions.
   * If a flag expects data, postfix it with ':' short opts and '=' for long opts.
   *
   * @param  string  $param    commandline parameter,
   *                           both long and short options are possible, seperated by comma.
   * @param  string  $varname  name of option in args -- Should be in CAPS by convention.
   * @param  string  $help     helpstring.
   * @param  boolean $quiet    Don't add param to help, add it only for compatability.
   * @return void
   */
  protected function addParam($param, $varname, $help, $quiet = false) {
    if ($quiet) {
      $help = $quiet;
    }
    $argreq = (strpos($param[0], ':') > 1) ? Getopt::REQUIRED_ARGUMENT : Getopt::NO_ARGUMENT;
    $short = (strlen(trim($param[0],':-')) == 1) ? trim($param[0],':-') : null;
    $long = (isset($param[1])) ? trim($param[1], ':-') : null;
    if (!$long) {
      $long = (strlen(trim($param[0],':-')) > 1) ? trim($param[0],':-') : null;
    }
    $this->structparams[] = new Option($short, $long, $argreq);
    $this->params[implode(',', $param)] = array('varname' => $varname,
                                  'help'    => $help);
  }

  /**
   * addUsage -- add a custom usage string.
   * completely optional as the usage is usally generated automatically.
   *
   * @param  string $usage whatever you want to write.
   * @return void
   */
  protected function addUsage($usage) {
    if (substr($usage, -1, 1) != '\n') {
      $usage .= "\n";
    }
    $this->usage = $usage;
  }

  /**
   * showHelp -- echoes the built helpstring to stdout.
   *
   * @return void
   */
  protected function showHelp() {
    echo $this->helpstring;
  }

  /**
   * setCommand -- set the commandname. used for the usage string.
   * should be the same as the file name
   *
   * @param string $name
   * @return void
   */
  protected function setCommand($name) {
    $this->cmdname = $name;
  }

  /**
   * getArgs -- used in the constructor to gather the commandline flags and options.
   * Uses Console_Getopt.
   *
   * @return array
   */
  private function getArgs() {
    $getopt = new Getopt($this->structparams);
    $getopt->parse();
    $args = $getopt->getOptions();
    return $args;
  }

  /**
   * condenseArgs -- helper function for getArgs()
   *
   * @param  mixed $params gathered data from th commandline
   * @return mixed         array of arguments and values.
   */
  private function condenseArguments($params) {
    $new_params = array();
    foreach ($params[0] as $param) {
      $val = ($param[1] == null) ? "1" : $param[1];
      $new_params[$param[0]] = $val;
    }

    return $new_params;
  }

  /**
   * getArgument -- Accessor for options
   *
   * @param  string $name varname
   * @return string       data
   */
  protected function getArgument($name) {
    return $this->args[$name];
  }

  /**
   * setArgument -- Accessor for options.
   * added to enable the use of the class outside the direct commandline context.
   *
   * @param  string $name varname
   * @param  string $data data
   */
  protected function setArgument($name, $data) {
    $this->args[$name] = $data;
  }

  /**
   * readInteractive -- utility function to get user input during script execution.
   *
   * @return string
   */
  protected function readInteractive() {
    $fp=fopen("/dev/stdin", "r");
    $input=fgets($fp, 255);
    fclose($fp);
    return str_replace("\n", "", $input);
  }

}
