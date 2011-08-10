<?php 
require_once('config.php');
require_once('ebook.cls.php');
require_once('library.cls.php');

class EbookLib() {
  
  private $request = 'list';
  
  private $requestId = 'date';
  
  private $requestParam;

  private $db = new library();

  public function parsePath() {
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $this->request = $path[0];
    $this->requestId = $path[1];
    $this->requestParam = $path[2];
  }
  
  public function handleRequest($displaytype = 'Browser') {
    $method = 'handle' + strtoupper($this->request);
    $display = new $displaytype;
    if(method_exists($this, $method)) {
      $this->$method($display);
    } else {
      $this->handleLIST($display);
    }
  }
  
  private function handleLIST($display) {
    switch($this->requestId) {
     case 'author':
      
     case date:
    }
  }
  
  private function handleREAD() {}
  
  private function handleGET() {}
  
  private function handleSHOW() {}
  
  private function handleAUTHOR() {}
  
  private function handleTAG() {}
  
  private function handleREAD() {}
  
  private function handleDELETE() {} 
  
  

}