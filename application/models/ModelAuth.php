<?php

namespace App\Models;

use CI_Model;
use Exception;

class ModelAuth extends CI_Model
{
    // protected $table = "authenticate";
    // protected $primarykey = "id";
    // protected $allowedFields = ['email', 'password'];

    public function login($email, $password)
    {
        $this->db->select('*');
        $this->db->from('authenticate');
        $this->db->where('email', $email);
        $this->db->where('password', $password);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return "User not found!";
        }
    }
}