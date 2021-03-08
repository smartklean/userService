<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UsersTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
//     public function testCreateUser()
//     {
//         $user = User::factory()->raw();
//
//         $this->post('/api/v1/users/create', $user);
//
//         $this->seeStatusCode(201);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testUpdateUser()
//     {
//         $user = User::factory()->raw();
//
//         $user = User::create($user);
//
//         $newData = User::factory()->raw();
//
//         $this->put('/api/v1/users/'.$user->id.'/update', $newData);
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testDeleteUser()
//     {
//         $user = User::factory()->raw();
//
//         $user = User::create($user);
//
//         $this->delete('/api/v1/users/'.$user->id.'/delete');
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testAuthenticateValidUser()
//     {
//         $user = User::factory()->raw();
//
//         $user = User::create($user);
//
//         $this->post('/api/v1/users/authenticate', [
//           'email' => $user->email,
//           'password' => 'password'
//         ]);
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testAuthenticateInvalidUser()
//     {
//         $user = User::factory()->raw();
//
//         $user = User::create($user);
//
//         $this->post('/api/v1/users/authenticate', [
//           'email' => $user->email,
//           'password' => 'incorrect_password'
//         ]);
//
//         $this->seeStatusCode(401);
//         $this->seeJsonStructure([
//           'status',
//           'message'
//         ]);
//     }
//
//     public function testFetchSingleUser()
//     {
//         $user = User::factory()->raw();
//
//         $user = User::create($user);
//
//         $this->get('/api/v1/users/'.$user->id.'/get');
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testFetchAllUsers()
//     {
//         $this->get('/api/v1/users/get');
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
//
//     public function testFetchAllUsersWithPagination()
//     {
//         $this->get('/api/v1/users/get?limit=3');
//
//         $this->seeStatusCode(200);
//         $this->seeJsonStructure([
//           'status',
//           'data',
//           'message'
//         ]);
//     }
}
