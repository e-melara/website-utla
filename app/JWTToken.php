<?php
  namespace App;

  use Firebase\JWT\JWT;

  class JWTToken {
    private $aud = null;
    private $encrypt = ['HS256'];
    private $SECRET = "r9dZ2FeUfdDW6ZOy8Kw9hYrdaR8OKl496Ch8dJ2zVnYKnnjJAFIA2UxF0U5FExhp";

    public function signIn($data = array())
    {
      $time = time();
      $token = [
        'exp' => $time + (60*60),
        'aud' => self::Aud(),
        'data' => $data
      ];

      return JWT::encode($token, $this->SECRET);
    }

    public function check($token)
    {
      if(empty($token)) {
        throw new Exception("Invalid token supplied.");
      }
      $decode = JWT::decode($token, $this->SECRET, $this->encrypt);
      if($decode->aud !== self::Aud()) {
        throw new Exception("Invalid user logged in.");
      }
    }

    public function data($token)
    {
      return JWT::decode(
        $token, 
        $this->SECRET,
        $this->encrypt
      )->data;
    }

    // Metodos privados
    private static function Aud()
    {
      $aud = '';
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $aud = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
          $aud = $_SERVER['REMOTE_ADDR'];
      }

      $aud .= @$_SERVER['HTTP_USER_AGENT'];
      $aud .= gethostname();
      return sha1($aud);
    }
  }
?>