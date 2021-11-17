<?php

use App\Models\Loan;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminTest extends TestCase
{
    use DatabaseMigrations;
    public $headers = [];
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->runDatabaseMigrations();
        $this->headers['Accept'] = 'application/json';
        $this->user = User::factory()->create([
            'username' => 'admin',
            'name' => 'Admin',
            'type' => \App\Models\User::TYPE_ADMIN,
            'password' => Hash::make('123456'),
        ]);

        $this->setHeaders($this->user);
    }

    public function setHeaders($user)
    {
        $token = JWTAuth::fromUser($user);
        $this->headers['Authorization'] = 'Bearer '.$token;
    }

    public function testGetMe()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->get('/api/v1/admin/auth/me', $this->headers)
            ->seeJson([
                "name" => "Admin",
            ]);
    }

    public function testApproveFail()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => APPROVE
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/admin/loans/approve/2',
                [

                ],
                $this->headers
            )
            ->seeJson([
                'code' => 400,
                'success' => false,
            ]);
    }

    public function testApproveSuccess()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => PENDING
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/admin/loans/approve/2',
                [],
                $this->headers
            )
            ->seeJson([
                'code' => 200,
                'status' => APPROVE,
            ]);
    }

    public function testRejectFail()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => APPROVE
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/admin/loans/reject/2',
                [
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 400,
                'success' => false,
            ]);
    }

    public function testRejectSuccess()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => PENDING
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/admin/loans/reject/2',
                [],
                $this->headers
            )
            ->seeJson([
                'code' => 200,
                'status' => REJECT,
            ]);
    }
}
