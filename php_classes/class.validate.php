<?php

Class Validate {

  public static function validate_login($arr) {
    $gump = new GUMP();
    // set validation rules
    $gump->validation_rules([
        'username'    => 'required|alpha_numeric_dash|max_len,36|min_len,5',
        'password'    => 'required|alpha_numeric_dash|max_len,36|min_len,5'
    ]);
    //  run gump
    $valid_data = $gump->run($arr);
    //  return depending on result
    if ($gump->errors()) {
        return array( 'is_valid' => false, 'errors' => $gump->get_errors_array());
    } else {
        return array( 'is_valid' => true, 'errors' => null);
    }
  }
  
}