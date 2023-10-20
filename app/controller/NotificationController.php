<?php

namespace App\controller;

use App\classes\Select;


class NotificationController extends Select {
// get all the notifications and show on the profile page

  public static function index(){

    $query = Select::formAndMatchQuery(selection:'SELECT_ALL', table:'notification');
    return Select::selectFn2($query);
  }

}