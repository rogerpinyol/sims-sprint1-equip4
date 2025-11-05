<?php
class PageController {
  public function home(): void {
    include __DIR__ . '/../views/landingpage.php';
  }
  public function terms(): void {
    include __DIR__ . '/../views/terms.php';
  }
  public function privacy(): void {
    include __DIR__ . '/../views/privacy.php';
  }
}