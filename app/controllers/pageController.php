<?php
class PageController {
  public function home(): void {
    include dirname(__DIR__, 2) . '/public/landingpage.php';
  }
  public function terms(): void {
    include dirname(__DIR__, 2) . '/public/privacy.php';
  }
  public function privacy(): void {
    include dirname(__DIR__, 2) . '/public/terms.php';
  }
}