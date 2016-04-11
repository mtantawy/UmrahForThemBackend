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
    private $access_token;
    private $created_user_id;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker::create();
        $faker = $this->faker;

        // adding a deceased creating a deceased and a umrah
        extract($this->create_user_and_get_access_token());
        $this->access_token = $access_token;
        $this->created_user_id = $created_user_id;
    }

    /**
     * @test
     * test ability to add deceased
     * @method can_add_deceased
     * @return [void]
     */
    public function can_add_deceased()
    {
        $parameters = [
            'name'      =>  $this->faker->name,
            'sex'       =>  $this->faker->boolean(50) ? 'male' : 'female',
            'age'       =>  $this->faker->numberBetween(1, 100),
            'country'   =>  $this->faker->country,
            'city'      =>  $this->faker->city,
            'death_cause'   =>  $this->faker->text,
            'death_date'    =>  $this->faker->date('Y-m-d', 'now'),
            'user_id'   =>  $this->created_user_id,
        ];
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
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

    /**
     * @test
     * test getting deceased with no umrahs (no umrahs with status done or in progress)
     * @method can_get_deceased_with_no_umrahs
     * @return [void]
     */
    public function can_get_deceased_with_no_umrahs()
    {
        // create deceased
        $deceased_no_umrah = factory(App\Deceased::class)->create();
        $deceased_with_umrah = factory(App\Deceased::class)->create();
        $deceased_with_umrah->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make());

        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/', [], [], [], $headers);
        $this->assertResponseOk($response);

        // checking on id because any other faked data can be repeated
        $this->seeJson(['id' => $deceased_no_umrah->toArray()['id']]);
        $this->dontSeeJson(['id' => $deceased_with_umrah->toArray()['id']]);
    }

    /**
     * @test
     * test getting requests (deceased/umrahs) requested by the authenticated user
     * @method can_get_my_requests
     * @return [void]
     */
    public function can_get_my_requests()
    {
        // create deceased by user A
        $deceased_user_a = factory(App\Deceased::class)->create(['user_id'  =>  $this->created_user_id]);
        // create deceased by user B
        $deceased_user_b = factory(App\Deceased::class)->create();
        // make a call while authenticated as user A
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/myrequests', [], [], [], $headers);
        $this->assertResponseOk($response);

        // checking on id because any other faked data can be repeated
        // see only deceased added by user A
        // don't see deceased added by user B
        $this->seeJson(['id' => $deceased_user_a->toArray()['id']]);
        $this->dontSeeJson(['id' => $deceased_user_b->toArray()['id']]);
    }

    /**
     * @test
     * test getting all deceased info correctly
     * @method can_get_deceased_details_with_umrah
     * @return [void]
     */
    public function can_get_deceased_details_with_umrah()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased
        $deceased->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make());
        // get deceased details
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for deceased details and umrah details
        $this->seeJson([
            'id'    =>  $deceased->id,
            'creator'   =>  [
                'user_id'   =>  $deceased->user->id,
                'name'      =>  $deceased->user->name,
                'email'     =>  $deceased->user->email,
            ],
            'umrahs'    =>  [
                [
                    'id'    =>  $deceased->umrahs()->first()->id,
                    'created_at'    =>  $deceased->umrahs()->first()->created_at->toDateTimeString(),
                    'updated_at'    =>  $deceased->umrahs()->first()->updated_at->toDateTimeString(),
                    'performer'     =>  [
                        'id'    =>  $deceased->umrahs()->first()->user->id,
                        'name'  =>  $deceased->umrahs()->first()->user->name,
                        'email' =>  $deceased->umrahs()->first()->user->email,
                    ],
                    'umrah_status'  =>  [
                        'id'    =>  $deceased->umrahs()->first()->umrahStatus->id,
                        'status'    =>  $deceased->umrahs()->first()->umrahStatus->status,
                        'created_at'    =>  $deceased->umrahs()->first()->umrahStatus->created_at->toDateTimeString(),
                        'updated_at'    =>  $deceased->umrahs()->first()->umrahStatus->updated_at->toDateTimeString(),
                    ],

                ]
            ],
        ]);
    }

    public function can_start_umrah()
    {
        // add deceased by random user
        // create umrah for this deceased and authenticated user, note umrah id
        // get this deceased info
        // check for umrah id & optionally (deceased id + user id)
    }

    public function can_update_umrah_status_to_done()
    {
        // add deceased by random user
        // start umrah for that deceased by authenticated user
        // change umrah status to done
        // get deceased details and check for umrah status
    }

    public function can_update_umrah_status_to_in_progress()
    {
        // add deceased by random user
        // start umrah for that deceased by authenticated user
        // change umrah status to in progress
        // get deceased details and check for umrah status
    }

    public function can_edit_deceased_details()
    {
        // add deceased by authenticated user
        // edit deceased details
        // get deceased details
        // check for new deceased details
    }
}
