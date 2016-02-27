<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class staticPagesTest extends TestCase
{
    use WithoutMiddleware;
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHomepage()
    {
        $this->visit('/')
        	->see('Umrah For The')
        	->dontSee('Laravel');	
    }

    public function testApiHomepage()
    {
    	$this->visit('/api')
    		->see('API Home');
    }

    public function testApiV1Homepage()
    {
    	$this->visit('/api/v1')
    		->see('api v1');
    }
}
