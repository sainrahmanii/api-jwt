<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';
require APPPATH . '/libraries/JWK.php';

// use namespace

use App\Models\ModelAuth;
use appilcation\libraries\REST_Controller;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Kontak extends REST_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

    function configToken()
    {
        $cnf['exp'] = 3600;
        $cnf['secretkey'] = '2212336221';
        return $cnf;
    }

    public function get_post()
    {
        $exp = time() + 3600;
        $token = array(
            "iss" => 'apprestservice',
            "aud" => 'pengguna',
            "iat" => time(),
            "nbf" => time() + 10,
            "exp" => $exp,
            "data" => array(
                "email" => $this->input->post('email'),
                "password" => $this->input->post('password')
            )
        );

        $jwt = JWT::encode($token, $this->configToken()['secretkey']);
        $output = [
            'status' => 200,
            'message' => 'Berhasil login',
            "token" => $jwt,
            "expireAt" => $token['exp']
        ];
        $data = array('kode' => '200', 'pesan' => 'token', 'data' => array('token' => $jwt, 'exp' => $exp));
        $this->response($data, 200);
    }

    public function authtoken()
    {
        $secret_key = $this->configToken()['secretkey'];
        $token = null;
        $authHeader = $this->input->request_headers()['Authorization'];
        $arr = explode(" ", $authHeader);
        $token = $arr[1];
        if ($token) {
            try {
                $decoded = JWT::decode($token, $this->configToken()['secretkey'], array('HS256'));
                if ($decoded) {
                    return 'true';
                }
            } catch (\Exception $e) {
                $result = array('message' => 'Kode Signature Tidak Sesuai');
                return 'false';
            }
        }
    }

    public function index_get()
    {
        // Users from a data store e.g. database

        $id = $this->get('id');

        // If the id parameter doesn't exist return all the users

        if ($id === NULL) {
            // if ($this->authtoken() == 'false') {
            //     return $this->response(array('code' => '401', 'message' => 'signature tidak sesuai', 'data' => []), '401');
            //     die();
            // }

            $users = $this->db->get("kontak")->result_array();

            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users) {
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.
        else {

            // Validate the id.
            if ($id <= 0) {
                // Invalid id, set the response and exit.
                $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
            }

            // Get the user from the array, using the id as key for retrieval.
            // Usually a model is to be used for this.

            $this->db->where(array("id" => $id));
            $users = $this->db->get("kontak")->row_array();

            $this->response($users, REST_Controller::HTTP_OK);
        }
    }

    public function index_post()
    {
        // $this->some_model->update_user( ... );
        $data = [
            'name' => $this->post('name'),
            'no_wa' => $this->post('no_wa'),
            'alamat' => $this->post('alamat')
        ];

        $this->db->insert("kontak", $data);

        $this->set_response($data, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function index_delete()
    {
        $id = (int) $this->delete('id');

        // Validate the id.
        if ($id <= 0) {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];

        $where = [
            'id'    => $id
        ];

        $this->db->delete("kontak", $where);

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

    public function index_put()
    {
        $where = array(
            "id"    => $this->put("id")
        );

        $data = array(
            "name"    => $this->put("name"),
            "no_wa"    => $this->put("no_wa"),
            "alamat"    => $this->put("alamat"),
        );

        $this->db->update("kontak", $data, $where);

        $this->set_response($data, REST_Controller::HTTP_CREATED);
    }
}
