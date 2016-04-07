<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use Faker\Factory as Faker;

class UmrahTest extends TestCase
{
    use DatabaseTransactions;

    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker::create();
        $faker = $this->faker;
    }

    /**
     * @test
     * test ability to add deceased
     * @method can_add_deceased
     * @return [void]
     */
    public function can_add_deceased()
    {
        // adding a deceased creating a deceased and a umrah
        extract($this->create_user_and_get_access_token());

        $parameters = [
            'name'      =>  $this->faker->name,
            'sex'       =>  $this->faker->boolean(50) ? 'male' : 'female',
            'age'       =>  $this->faker->numberBetween(1, 100),
            'country'   =>  $this->faker->country,
            'city'      =>  $this->faker->city,
            'death_cause'   =>  $this->faker->text,
            'death_date'    =>  $this->faker->date('Y-m-d', 'now'),
            'user_id'   =>  $created_user_id,
        ];
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$access_token,
            ]);
        $response = $this->call('POST', '/api/v2/umrah/', $parameters, [], [], $headers);
        $this->assertResponseOk($response);
        // check seeInDatabase
        $this->seeInDatabase(
            'deceased',
            $parameters
        );
        // modify parameters for response check
        $parameters['creator_id'] = $parameters['user_id'];
        unset($parameters['user_id']);
        $this->seeJson($parameters);
    }

    private function create_user_and_get_access_token()
    {
        $client_data = [
            'id'    =>  $this->faker->randomNumber,
            'secret'    =>  'secret',
            'name'  =>  'client_name'
        ];
        \DB::table('oauth_clients')->insert($client_data);

        // create user in DB
        $password = bcrypt(str_random(10));
        $user = [
            'name'  =>  $this->faker->name,
            'email' =>  $this->faker->email,
            'password'  =>  bcrypt($password)
        ];
        $created_user = User::Create($user);
        // try to login user
        $login_agrs = [
            'grant_type'    =>  'password',
            'client_id'     =>  $client_data['id'],
            'client_secret' =>  'secret',
            'username'      =>  $user['email'],
            'password'      =>  $password,
        ];
        $response = $this->call('POST', '/api/v2/login', $login_agrs);
        $this->assertResponseOk($response);

        $access_token = $response->getData()->access_token;

        return [
            'access_token'  =>  $access_token,
            'created_user_id'   =>  $created_user->id,
        ];
    }
}
