<?php
use Phalcon\Mvc\Model;
class ChessMoves extends Model
{
    public $chess_moves_id;
    public $chess_id;
    public $user_id;
    public $camp;
    public $move;
    public $red_pace;
    public $all_red_pace;
    public $black_pace;
    public $all_black_pace;
}
